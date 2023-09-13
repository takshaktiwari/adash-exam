<?php

namespace Takshak\Exam\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\UserPaper;
use Illuminate\Support\Facades\View;

class UserPaperController extends Controller
{
    public function index(Paper $paper)
    {
        $paper->loadCount('questions');

        $userPapers = UserPaper::query()
            ->with('questions:id,user_paper_id,status,marks')
            ->where('paper_id', $paper->id)
            ->where('user_id', auth()->id())
            ->paginate(50);

        return View::first(['exam.user-papers.index', 'exam::exam.user-papers.index'])->with([
            'userPapers' =>  $userPapers,
            'paper' =>  $paper,
        ]);
    }

    public function show(Paper $paper, UserPaper $userPaper)
    {
        $paper->load(['questions' => function ($query) use ($userPaper) {
            $query->with('correctOption');
            $query->with(['userQuestion' => function ($query) use ($userPaper) {
                $query->where('user_paper_id', $userPaper->id);
            }]);
        }])
            ->loadCount('questions')
            ->loadSum('questions', 'marks');

        $userPaper->loadSum('questions', 'marks');

        return View::first(['exam.user-papers.show', 'exam::exam.user-papers.show'])->with([
            'userPaper' =>  $userPaper,
            'paper' =>  $paper,
        ]);
    }


}
