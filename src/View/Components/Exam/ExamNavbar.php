<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class ExamNavbar extends Component
{
    public function __construct() {}

    public function render()
    {
        return View::first([
            'components.exam.navbar',
            'exam::components.exam.navbar'
        ]);
    }
}
