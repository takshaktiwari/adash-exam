<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\View;

class UserExamProgressController extends Controller
{
    public function index()
    {
        $selectedUser = null;
        if (request('user_id')) {
            $selectedUser = User::find(request('user_id'));
        }

        $users = User::query()
            ->select('id', 'name', 'email')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return View::first(['admin.exam.user-exam-progress.index', 'exam::admin.exam.user-exam-progress.index'])
            ->with([
                'users' => $users,
                'selectedUser' => $selectedUser,
            ]);
    }
}
