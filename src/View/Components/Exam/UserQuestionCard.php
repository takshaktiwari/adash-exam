<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class UserQuestionCard extends Component
{
    public function __construct(public $question) {}

    public function render()
    {
        return View::first([
            'components.exam.user-question-card',
            'exam::components.exam.user-question-card'
        ]);
    }
}
