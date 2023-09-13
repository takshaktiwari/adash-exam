<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;

class AdminSidebarLinks extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return View::first([
            'components.exam.admin-sidebar-links',
            'exam::components.exam.admin-sidebar-links'
        ]);
    }
}
