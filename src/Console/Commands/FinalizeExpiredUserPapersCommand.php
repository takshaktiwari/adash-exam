<?php

namespace Takshak\Exam\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Takshak\Exam\Services\ExamScoringService;

/**
 * Scores attempts whose time ran out but which were never submitted.
 *
 * Students routinely leave an exam without pressing Submit — the timer runs out, the
 * tab is closed, the connection drops. Scoring used to happen only inside the submit
 * request, so those attempts kept the raw face-value marks recorded as each question
 * was answered, and every report that sums them showed an inflated score (in the worst
 * case a full-marks result on a paper the student mostly got wrong).
 *
 * Running this on a schedule means an attempt is always scored eventually, whether or
 * not the student ever pressed the button.
 */
class FinalizeExpiredUserPapersCommand extends Command
{
    protected $signature = 'exam:finalize-expired-user-papers {--dry-run : Report what would be finalized without changing anything}';

    protected $description = 'Score exam attempts whose time has expired but which were never submitted';

    public function handle(ExamScoringService $examScoring): int
    {
        if ($this->option('dry-run')) {
            $pending = DB::table('user_papers')
                ->whereNull('submit_at')
                ->where('end_at', '<', now())
                ->count();

            $inflatedRows = DB::table('user_questions as uq')
                ->join('user_papers as up', 'up.id', '=', 'uq.user_paper_id')
                ->whereNull('up.submit_at')
                ->where('up.end_at', '<', now())
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('uq.status', 'answered')
                            ->whereColumn('uq.user_option_id', '!=', 'uq.correct_option_id')
                            ->where('uq.marks', '>', 0);
                    })->orWhere(function ($q) {
                        $q->where('uq.status', '!=', 'answered')
                            ->where('uq.marks', '!=', 0);
                    });
                })
                ->count();

            $this->warn("[dry run] Would finalize {$pending} expired attempt(s), correcting {$inflatedRows} answer row(s).");

            return self::SUCCESS;
        }

        $finalized = $examScoring->finalizeExpiredUserPapers();
        $this->info("Finalized {$finalized} expired unsubmitted attempt(s).");

        // Runs regardless of the above: a straggler answer can land just after submit
        // on an attempt that was otherwise scored normally.
        $swept = $examScoring->sweepRecentlySubmitted();
        $this->info("Re-scored {$swept} recently submitted attempt(s) containing unscored answers.");

        return self::SUCCESS;
    }
}
