<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\UserPaper;

class UserPaperController extends Controller
{
    public function index(Request $request)
    {
        $papers = collect();
        $users = collect();
        if (request('filter')) {
            $papers = Paper::select('id', 'title')->get();
            $users = User::select('id', 'name')->get();
        }

        $userPapers = UserPaper::query()
            ->with(['paper' => function ($query) {
                $query->withCount('questions');
            }])
            ->with('questions:id,user_paper_id,status,marks')
            ->when($request->get('paper_id'), function ($query) use ($request) {
                $query->where('paper_id', $request->get('paper_id'));
            })
            ->when($request->get('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->get('user_id'));
            })
            ->when($request->get('started_on'), function ($query) use ($request) {
                $query->whereDate('start_at', $request->get('started_on'));
            })
            ->latest()
            ->paginate(50);

        return View::first(['admin.exam.user-papers.index', 'exam::admin.exam.user-papers.index'])->with([
            'userPapers' =>  $userPapers,
            'papers' =>  $papers,
            'users' =>  $users,
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

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_paper_ids' => 'required'
        ]);

        $user_paper_ids = explode(',', $request->user_paper_ids);
        if (count($user_paper_ids)) {
            UserPaper::whereIn('id', $user_paper_ids)->delete();
        }

        return redirect()->route('admin.exam.user-papers.index')->withSuccess('SUCCESS !! Selected questions are successfully deleted');
    }
}
