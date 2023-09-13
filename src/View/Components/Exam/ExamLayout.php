<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class ExamLayout extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return View::first([
            'layouts.exam',
            'exam::layouts.exam'
        ]);
    }
}
