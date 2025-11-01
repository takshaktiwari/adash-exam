<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use Takshak\Exam\DataTables\UserPapersDataTable;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\UserPaper;

class UserPaperController extends Controller
{
    public function index(UserPapersDataTable $dataTable, Request $request)
    {
        $papers = collect();
        $users = collect();
        if (request('filter')) {
            $papers = Paper::select('id', 'title')->get();
            $users = User::select('id', 'name')->get();
        }

        return $dataTable->render(
            View::first([
                'admin.exam.user-papers.index',
                'exam::admin.exam.user-papers.index'
            ])->name(),
            [
                'papers' =>  $papers,
                'users' =>  $users,
            ]
        );
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
            'item_ids' => 'required|array'
        ]);

        if (count($request->item_ids)) {
            UserPaper::whereIn('id', $request->item_ids)->delete();
        }

        return redirect()->route('admin.exam.user-papers.index')->withSuccess('SUCCESS !! Selected questions are successfully deleted');
    }
}
