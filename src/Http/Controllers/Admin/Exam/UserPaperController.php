<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\UserPaper;

class UserPaperController extends Controller
{
    public function index(Request $request)
    {
        $userPapers = UserPaper::query()
            ->with(['paper' => function($query){
                $query->withCount('questions');
            }])
            ->with('questions:id,user_paper_id,status,marks')
            ->latest()
            ->paginate(50);

        return View::first(['admin.exam.user-papers.index', 'exam::admin.exam.user-papers.index'])->with([
            'userPapers' =>  $userPapers,
        ]);
    }

    public function show(UserPaper $userPaper)
    {
        $paper = $userPaper->paper;

        $paper->load(['questions' => function ($query) use ($userPaper) {
            $query->with('correctOption');
            $query->with(['userQuestion' => function ($query) use ($userPaper) {
                $query->where('user_paper_id', $userPaper->id);
            }]);
        }])
            ->loadCount('questions')
            ->loadSum('questions', 'marks');

        $userPaper->loadSum('questions', 'marks');


        return View::first(['admin.exam.user-papers.show', 'exam::admin.exam.user-papers.show'])->with([
            'userPaper' =>  $userPaper,
            'paper' =>  $paper,
        ]);
    }

    public function destroy(UserPaper $userPaper)
    {
        $userPaper->delete();
        return redirect()->route('admin.exam.user-papers.index')->withSuccess('SUCCESS !! User exam has been deleted.');
    }
}
