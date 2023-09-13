<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;

class PaperController extends Controller
{
    public function index(Request $request)
    {
        $papers = Paper::query()
            ->withCount('sections')
            ->withCount('questions')
            ->withSum('questions', 'marks')
            ->orderBy('id', 'ASC')
            ->paginate(25);

        return View::first(['admin.exam.papers.index', 'exam::admin.exam.papers.index'])
            ->with([
                'papers'   =>  $papers
            ]);
    }

    public function create()
    {
        return View::first(['admin.exam.papers.create', 'exam::admin.exam.papers.create']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         =>  'required|max:254|unique:papers,title',
            'total_time'    =>  'required|numeric',
            'activate_at'   =>  'required|date',
            'expire_at'     =>  'required|date',
            'minus_mark_percent' =>  'nullable|numeric',
            'instruction' =>  'required|string',
        ]);

        $paper = Paper::create([
            'title'     =>  $request->post('title'),
            'total_time'     =>  $request->post('total_time'),
            'activate_at'     =>  $request->post('activate_at'),
            'expire_at'     =>  $request->post('expire_at'),
            'minus_mark_percent'     =>  $request->post('minus_mark_percent'),
            'instruction'     =>  $request->post('instruction'),
            'status'     =>  $request->boolean('status'),
        ]);

        if ($request->post('has_sections')) {
            return redirect()->route('admin.exam.papers.sections.index', [$paper])->withErrors('CREATED !! New paper has been successfully created. Add some questions to this set');
        }

        return redirect()->route('admin.exam.papers.questions.edit', [$paper]);
    }

    public function show(Paper $paper)
    {
        $paper->loadSum('questions', 'marks')
            ->load(['sections' => function ($query) {
                $query->withCount('questions');
            }]);

        return View::first(['admin.exam.papers.show', 'exam::admin.exam.papers.show'])
            ->with([
                'paper'   =>  $paper
            ]);
    }

    public function edit(Paper $paper)
    {
        return View::first(['admin.exam.papers.edit', 'exam::admin.exam.papers.edit'])->with([
            'paper' =>  $paper
        ]);
    }

    public function update(Request $request, Paper $paper)
    {
        $request->validate([
            'title'         =>  'required|max:254|unique:papers,title,' . $paper->id,
            'total_time'    =>  'required|numeric',
            'activate_at'   =>  'required|date',
            'expire_at'     =>  'required|date',
            'minus_mark_percent' =>  'nullable|numeric',
            'instruction' =>  'required|string',
        ]);

        $paper->update([
            'title'     =>  $request->post('title'),
            'total_time'     =>  $request->post('total_time'),
            'activate_at'     =>  $request->post('activate_at'),
            'expire_at'     =>  $request->post('expire_at'),
            'minus_mark_percent'     =>  $request->post('minus_mark_percent'),
            'instruction'     =>  $request->post('instruction'),
            'status'     =>  $request->boolean('status'),
        ]);

        return redirect()->route('admin.exam.papers.show', [$paper])->withErrors('Updated !! Paper has been successfully created. ');
    }

    public function destroy(Paper $paper)
    {
        $paper->delete();
        return redirect()->route('admin.exam.papers.index')->withErrors('DELETED !! Paper is successfully deleted');
    }

    public function questionsEdit(Paper $paper)
    {
        return View::first(['admin.exam.papers.questions_edit', 'exam::admin.exam.papers.questions_edit'])->with([
            'paper' =>  $paper
        ]);
    }

    public function questionsUpdate(Request $request, Paper $paper)
    {
        $request->validate([
            'questions' =>  'required|array'
        ]);

        $paper->questions()->sync($request->questions);
        return back();
    }
}
