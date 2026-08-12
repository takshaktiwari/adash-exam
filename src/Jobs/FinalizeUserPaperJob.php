<?php

namespace Takshak\Exam\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Takshak\Exam\Services\ExamScoringService;

/**
 * Runs the actual scoring off the student's request.
 *
 * finalizeUserPaper() is a multi-table UPDATE...JOIN across user_questions/papers —
 * the heaviest write in the whole exam flow. Running it inline in submit() meant every
 * student paid that cost synchronously, and when many attempts share an end_at (a
 * whole class starting together), everyone's timer expires within the same second and
 * all of those writes land on the database at once, right as the server is already at
 * its busiest. Queuing it means the student gets their "submitted" response
 * immediately and the scoring work is spread out by the queue worker instead of
 * hitting the database as one simultaneous burst.
 *
 * Retries (not a DB::transaction retry) cover a lock-wait timeout or a transient lost
 * connection during that same burst, which would otherwise fail the job outright.
 */
class FinalizeUserPaperJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30];

    public function __construct(public int $userPaperId)
    {
    }

    public function handle(ExamScoringService $examScoring): void
    {
        $examScoring->finalizeUserPaper($this->userPaperId);
    }
}
