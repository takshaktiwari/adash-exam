<?php

namespace Takshak\Exam\Services;

use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for turning a student's raw answers into awarded marks.
 *
 * Background: `user_questions.marks` is written at answer time as a snapshot of the
 * question's face value (see ExamController::questionSave). It only becomes the
 * *awarded* score once scoring is applied. Historically that only ever happened
 * inside ExamController::submit(), so any attempt where the student never pressed
 * Submit (timer expired, tab closed, connection dropped) kept its raw face values
 * forever — and every consumer that sums that column (admin paper detail, the admin
 * user-papers DataTable, the student's result page, exam:prepare-user-paper-report,
 * and the rankings built from it) reported an inflated score.
 *
 * Both the interactive submit path and the scheduled finalizer for expired attempts
 * now route through this class so the two can never drift apart.
 *
 * Every statement here is idempotent: each one only matches rows that have not been
 * scored yet, and scoring a row moves it out of that set. Re-running is a no-op, so
 * it is safe to call on an already-finalized attempt.
 */
class ExamScoringService
{
    /**
     * Apply scoring to a single attempt. Safe to call more than once.
     */
    public function finalizeUserPaper(int $userPaperId): void
    {
        DB::transaction(function () use ($userPaperId) {
            $this->promoteMarkReview($userPaperId);
            $this->applyScoring($userPaperId);
        });
    }

    /**
     * Apply scoring to every attempt whose time is up but which was never submitted.
     *
     * In-progress attempts (end_at still in the future) are deliberately left alone —
     * a student mid-exam must not have their partial answers scored underneath them.
     *
     * @return int Number of attempts finalized.
     */
    public function finalizeExpiredUserPapers(): int
    {
        $userPaperIds = DB::table('user_papers')
            ->whereNull('submit_at')
            ->where('end_at', '<', now())
            ->pluck('id');

        if ($userPaperIds->isEmpty()) {
            return 0;
        }

        foreach ($userPaperIds->chunk(500) as $chunk) {
            $ids = $chunk->all();

            DB::transaction(function () use ($ids) {
                $this->promoteMarkReview($ids);
                $this->applyScoring($ids);

                // Stamp the attempt as concluded at its own deadline, which is when the
                // exam actually ended for that student. Without this the attempt stays
                // "in progress" forever and would be re-examined on every run.
                DB::table('user_papers')
                    ->whereIn('id', $ids)
                    ->whereNull('submit_at')
                    ->update([
                        'submit_at'  => DB::raw('end_at'),
                        'updated_at' => now(),
                    ]);
            });
        }

        return $userPaperIds->count();
    }

    /**
     * Re-score recently submitted attempts that still contain unscored rows.
     *
     * An answer saved in the same instant the student submits can land just after the
     * submit request has already read the rows it was going to score, leaving a single
     * row at its face value and quietly inflating that student's total. The window for
     * that race is seconds wide, so only recent attempts are worth re-checking; older
     * ones have long since settled.
     *
     * @param int|null $days How far back to look; null sweeps all history (used by a
     *                       one-off backfill, too slow for the hourly run).
     * @return int Number of attempts re-scored.
     */
    public function sweepRecentlySubmitted(?int $days = 7): int
    {
        $userPaperIds = DB::table('user_papers as up')
            ->join('user_questions as uq', 'uq.user_paper_id', '=', 'up.id')
            ->whereNotNull('up.submit_at')
            ->when($days !== null, fn($q) => $q->where('up.submit_at', '>=', now()->subDays($days)))
            ->where(function ($query) {
                // The two shapes an unscored row can take after submit.
                $query->where(function ($q) {
                    $q->where('uq.status', '!=', 'answered')
                        ->where('uq.marks', '!=', 0);
                })->orWhere(function ($q) {
                    $q->where('uq.status', 'answered')
                        ->whereColumn('uq.user_option_id', '!=', 'uq.correct_option_id')
                        ->where('uq.marks', '>', 0);
                });
            })
            ->distinct()
            ->pluck('up.id');

        if ($userPaperIds->isEmpty()) {
            return 0;
        }

        foreach ($userPaperIds->chunk(500) as $chunk) {
            $ids = $chunk->all();

            DB::transaction(function () use ($ids) {
                $this->promoteMarkReview($ids);
                $this->applyScoring($ids);
            });
        }

        return $userPaperIds->count();
    }

    /**
     * Answers parked as "review later" still count as answered once the exam is over.
     *
     * @param int|array<int> $userPaperIds
     */
    private function promoteMarkReview(int|array $userPaperIds): void
    {
        DB::table('user_questions')
            ->where('status', 'mark_review')
            ->when(
                is_array($userPaperIds),
                fn($q) => $q->whereIn('user_paper_id', $userPaperIds),
                fn($q) => $q->where('user_paper_id', $userPaperIds)
            )
            ->update(['status' => 'answered']);
    }

    /**
     * The scoring rules themselves.
     *
     * Correct answers already hold the right value (their face value) and are left
     * untouched. The remaining three cases are expressed as set-based statements so
     * this stays cheap over a large backfill as well as a single submit.
     *
     * `marks` is read as the face value here, which holds precisely because each
     * statement is restricted to not-yet-scored rows. Deriving from questions.marks
     * instead would retroactively rewrite historic scores whenever an admin edits a
     * question's marks after the fact.
     *
     * @param int|array<int> $userPaperIds
     */
    private function applyScoring(int|array $userPaperIds): void
    {
        $scope = fn($query, string $column = 'user_paper_id') => $query->when(
            is_array($userPaperIds),
            fn($q) => $q->whereIn($column, $userPaperIds),
            fn($q) => $q->where($column, $userPaperIds)
        );

        // 1. Anything not answered (skipped, or flagged without picking an option) scores nothing.
        $scope(
            DB::table('user_questions')
                ->where('status', '!=', 'answered')
                ->where('marks', '!=', 0)
        )->update(['marks' => 0, 'updated_at' => now()]);

        // 2. Wrong answers on a negative-marking paper lose the configured percentage.
        //    <=> is MySQL's NULL-safe equality, so a question with no correct option on
        //    record is still evaluated rather than silently skipped by a NULL comparison.
        DB::statement(
            'UPDATE user_questions uq
                JOIN papers p ON p.id = uq.paper_id
                SET uq.marks = -1 * (uq.marks * (p.minus_mark_percent / 100)),
                    uq.updated_at = ?
              WHERE uq.status = "answered"
                AND NOT (uq.user_option_id <=> uq.correct_option_id)
                AND uq.marks > 0
                AND p.minus_mark_percent > 0
                AND ' . $this->scopeSql($userPaperIds),
            [now()]
        );

        // 3. Wrong answers on a paper without negative marking simply score nothing.
        DB::statement(
            'UPDATE user_questions uq
                JOIN papers p ON p.id = uq.paper_id
                SET uq.marks = 0,
                    uq.updated_at = ?
              WHERE uq.status = "answered"
                AND NOT (uq.user_option_id <=> uq.correct_option_id)
                AND uq.marks <> 0
                AND (p.minus_mark_percent IS NULL OR p.minus_mark_percent = 0)
                AND ' . $this->scopeSql($userPaperIds),
            [now()]
        );
    }

    /**
     * Inline the attempt ids into the raw statements above. They come from the
     * database as integers, and are cast again here, so they are safe to embed.
     *
     * @param int|array<int> $userPaperIds
     */
    private function scopeSql(int|array $userPaperIds): string
    {
        $ids = collect((array) $userPaperIds)
            ->map(fn($id) => (int) $id)
            ->implode(',');

        return "uq.user_paper_id IN ({$ids})";
    }
}
