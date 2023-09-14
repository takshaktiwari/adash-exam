<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class Papers extends Component
{
    public function __construct(public $papers)
    {

    }

    public function render()
    {
        return View::first([
            'components.exam.papers',
            'exam::components.exam.papers'
        ]);
    }
}
