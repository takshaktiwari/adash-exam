<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class UserPapers extends Component
{
    public function __construct(public $paper, public $userPapers)
    {

    }

    public function render()
    {
        return View::first([
            'components.exam.user-papers',
            'exam::components.exam.user-papers'
        ]);
    }
}
