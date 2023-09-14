<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class UserPaperDetail extends Component
{
    public function __construct(public $paper, public $userPaper)
    {

    }

    public function render()
    {
        return View::first([
            'components.exam.user-paper-detail',
            'exam::components.exam.user-paper-detail'
        ]);
    }
}
