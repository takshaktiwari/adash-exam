<?php

namespace Takshak\Exam\View\Components\Exam;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Takshak\Exam\Models\UserPaperReport;

class UserExamRanking extends Component
{
    public $reports;
    public $rank;

    /**
     * Create a new component instance.
     */
    public function __construct(public $paper, public $userId)
    {
        $userReport = UserPaperReport::where('paper_id', $paper->id)
            ->where('user_id', $userId)
            ->orderBy('marks', 'DESC')
            ->first();

        if ($userReport) {
            $this->rank = UserPaperReport::where('paper_id', $paper->id)
                ->where('marks', '>', $userReport->marks)
                ->count() + 1;
        } else {
            $this->rank = '-';
        }

        $this->reports = UserPaperReport::query()
            ->select('id', 'user_id', 'paper_id', 'marks')
            ->with('user:id,name')
            ->where('paper_id', $paper->id)
            ->orderBy('marks', 'DESC')
            ->limit(50)
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): ViewContract|Closure|string
    {
        return View::first([
            'components.user-exam-ranking',
            'exam::components.user-exam-ranking',
        ]);
    }
}
