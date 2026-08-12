<?php

namespace Takshak\Exam\View\Components\Exam;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\View;
use Illuminate\View\Component;
use Takshak\Exam\Models\UserPaperReport;

class UserExamProgress extends Component
{
    public $userPapers;
    public $labels;

    /**
     * Create a new component instance.
     */
    public function __construct(public $userId, public $limit = 10)
    {
        $this->userPapers = UserPaperReport::query()
            ->where('user_id', $this->userId)
            ->with('userPaper')
            ->with('paper')
            ->orderBy('user_paper_at', 'DESC')
            ->limit($this->limit)
            ->get();

        $this->labels = $this->userPapers->map(function ($item) {
            return $item->paper->title . ' | ' . $item->user_paper_at->format('d-M-y');
        });
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): ViewContract|Closure|string
    {
        return View::first([
            'components.user-exam-progress',
            'exam::components.user-exam-progress',
        ]);
    }
}
