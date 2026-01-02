<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;

class PaperController extends Controller
{
    public function index(Request $request)
    {
        $papers = Paper::query()
            ->withCount('sections')
            ->withCount('questions')
            ->withSum('questions', 'marks')
            ->orderBy('id', 'DESC')
            ->paginate(50);

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
            'security_code' => 'nullable',
            'attempts_limit' => 'nullable|numeric',
        ]);

        $paper = Paper::create([
            'title'     =>  $request->post('title'),
            'total_time'     =>  $request->post('total_time'),
            'activate_at'     =>  $request->post('activate_at'),
            'expire_at'     =>  $request->post('expire_at'),
            'minus_mark_percent'     =>  $request->post('minus_mark_percent'),
            'instruction'     =>  $request->post('instruction'),
            'status'     =>  $request->boolean('status'),
            'shuffle_questions'     =>  $request->boolean('shuffle_questions'),
            'lock_sections'     =>  $request->boolean('lock_sections'),
            'security_code'     =>  $request->post('security_code'),
            'attempts_limit'     =>  $request->post('attempts_limit'),
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
            }])
            ->loadCount('questions');

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
            'security_code' => 'nullable',
            'attempts_limit' => 'nullable|numeric',
        ]);

        $paper->update([
            'title'     =>  $request->post('title'),
            'total_time'     =>  $request->post('total_time'),
            'activate_at'     =>  $request->post('activate_at'),
            'expire_at'     =>  $request->post('expire_at'),
            'minus_mark_percent'     =>  $request->post('minus_mark_percent'),
            'instruction'     =>  $request->post('instruction'),
            'status'     =>  $request->boolean('status'),
            'shuffle_questions'     =>  $request->boolean('shuffle_questions'),
            'lock_sections'     =>  $request->boolean('lock_sections'),
            'security_code'     =>  $request->post('security_code'),
            'attempts_limit'     =>  $request->post('attempts_limit'),
        ]);

        cache()->forget('paper_' . $paper->id);

        return redirect()->route('admin.exam.papers.show', [$paper])->withErrors('Updated !! Paper has been successfully created. ');
    }

    public function destroy(Paper $paper)
    {
        cache()->forget('paper_' . $paper->id);
        $paper->delete();
        return redirect()->route('admin.exam.papers.index')->withErrors('DELETED !! Paper is successfully deleted');
    }

    public function questionsEdit(Paper $paper)
    {
        $paper->loadCount('questions');
        $questionGroups = QuestionGroup::get();
        return View::first(['admin.exam.papers.questions_edit', 'exam::admin.exam.papers.questions_edit'])->with([
            'paper' =>  $paper,
            'questionGroups' => $questionGroups
        ]);
    }

    public function questionsUpdate(Request $request, Paper $paper)
    {
        $request->validate([
            'questions' =>  'required|array'
        ]);
        $paper->questions()->sync($request->questions);
        cache()->forget('paper_' . $paper->id);
        return back();
    }

    public function questionsAutoAdd(Request $request, Paper $paper)
    {
        $paper->load('questions:id');
        $questionIds = $paper->questions->pluck('id');

        $questions = Question::query()
            ->with('children')
            ->with('papers:id,title')
            ->with('questionGroups:id,name')
            ->when($request->get('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('question', 'LIKE', '%' . $request->search . '%');
                    $query->orWhereHas('parent', function ($query) use ($request) {
                        $query->where('question', 'LIKE', '%' . $request->search . '%');
                    });
                });
            })
            ->when($request->get('question_group_id'), function ($query) {
                $query->whereHas('questionGroups', function ($query) {
                    $query->where('question_groups.id', request('question_group_id'));
                });
            })
            ->when(request('not_used'), function ($query) use ($questionIds) {
                $query->doesntHave('papers');
            })
            ->parent()
            ->limit($request->get('auto_add_questions', 10))
            ->get()
            ->each(function ($question) use (&$questionIds) {
                $questionIds->push($question->id);
                if ($question->children) {
                    foreach ($question->children as $child) {
                        $questionIds->push($child->id);
                    }
                }
            });

        $questions = Question::whereIn('id', $questionIds)->get();
        $questionIds = $questions->pluck('id')->toArray();

        $paper->questions()->sync($questionIds);
        cache()->forget('paper_' . $paper->id);
        return back();
    }
}
