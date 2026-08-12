<?php

namespace Takshak\Exam\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Takshak\Exam\Models\UserPaper;
use Takshak\Exam\Models\UserPaperReport;

class PrepareUserPaperReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:prepare-user-paper-report {--user_id=} {--paper_id=} {--after_date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate user paper report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $searchDate = null;
        if (Storage::disk('local')->exists('last_report_run')) {
            $searchDate = Storage::disk('local')->get('last_report_run');
            $this->line('Generating report for user papers after: ' . $searchDate);
        }

        Storage::disk('local')->put('last_report_run', now()->subDay()->toDateTimeString());

        if ($this->option('after_date')) {
            $searchDate = Carbon::parse($this->option('after_date'))->toDateTimeString();
        }

        $users = User::query()
            ->when($this->option('user_id'), function ($query, $userId) {
                return $query->where('id', $userId);
            })
            ->where('status', true)
            ->get();

        foreach ($users as $user) {
            $userPapers = UserPaper::query()
                ->select('id', 'paper_id', 'user_id', 'created_at')
                ->with('questions')
                ->with('paper.questions')
                ->where('user_id', $user->id)
                ->when($this->option('paper_id'), function ($query, $paperId) {
                    return $query->where('paper_id', $paperId);
                })
                ->when($searchDate, function ($query, $searchDate) {
                    return $query->where('created_at', '>=', $searchDate);
                })
                ->get();

            $this->info('Generating report for user: ' . $user->name . ' => ' . $userPapers->count() . ' papers');

            foreach ($userPapers as $key => $userPaper) {
                $total_marks = $userPaper->paper->questions->sum('marks');
                $marks = $userPaper->questions->sum('marks');
                $percentage = $total_marks ? (($marks * 100) / $total_marks) : 0;
                UserPaperReport::updateOrCreate([
                    'user_paper_id' => $userPaper->id,
                    'user_id' => $user->id,
                    'paper_id' => $userPaper->paper_id,
                ], [
                    'questions' => $userPaper->paper->questions->count(),
                    'answered' => $userPaper->questions->where('status', 'answered')->count(),
                    'correct' => $userPaper->questions->filter(fn($i) => $i->isCorrect)->count(),
                    'incorrect' => $userPaper->questions->filter(fn($i) => !$i->isCorrect)->count(),
                    'total_marks' => $total_marks,
                    'marks' => $marks,
                    'percentage' => $percentage,
                    'user_paper_at' => $userPaper->created_at
                ]);

                $this->line("    " . ($key + 1) . ") Paper: " . $userPaper->paper->title);
            }

            $this->newLine();
        }
    }
}
