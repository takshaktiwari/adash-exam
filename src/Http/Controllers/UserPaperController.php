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
        $paper->loadCount('questions')
            ->loadCount('sections')
            ->loadSum('questions', 'marks');

        if($paper->sections_count) {
            $paper->load(['sections' => function ($query) use ($userPaper) {
                $query->with(['questions' => function ($query) use ($userPaper) {
                    $query->with('correctOption');
                    $query->with(['userQuestion' => function ($query) use ($userPaper) {
                        $query->where('user_paper_id', $userPaper->id);
                    }]);
                }]);
            }]);
        } else {
            $paper->load(['questions' => function ($query) use ($userPaper) {
                $query->with('correctOption');
                $query->with(['userQuestion' => function ($query) use ($userPaper) {
                    $query->where('user_paper_id', $userPaper->id);
                }]);
            }]);
        }

        $userPaper->loadSum('questions', 'marks');

        return View::first(['exam.user-papers.show', 'exam::exam.user-papers.show'])->with([
            'userPaper' =>  $userPaper,
            'paper' =>  $paper,
        ]);
    }
}
