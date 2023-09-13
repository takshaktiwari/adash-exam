<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionGroup;

class PaperSectionController extends Controller
{
    public function index(Paper $paper)
    {
        $paper->load(['sections' => function ($query) {
            $query->withCount('questions');
        }]);

        return View::first(['admin.exam.papers.sections.index', 'exam::admin.exam.papers.sections.index'])
            ->with([
                'paper'   =>  $paper
            ]);
    }

    public function store(Request $request, Paper $paper)
    {
        foreach ($request->post('sections') ?? [] as $id => $section) {
            PaperSection::where('id', $id)->update(['name'  =>  $section]);
        }

        foreach ($request->post('new_sections') ?? [] as $section) {
            PaperSection::create([
                'paper_id' => $paper->id,
                'name'  =>  $section
            ]);
        }

        return redirect()->route('admin.exam.papers.sections.index', [$paper]);
    }

    public function show(Paper $paper, PaperSection $section)
    {
        $questionGroups = QuestionGroup::get();
        return View::first(['admin.exam.papers.sections.show', 'exam::admin.exam.papers.sections.show'])
            ->with([
                'paper'   =>  $paper,
                'section' => $section,
                'questionGroups' => $questionGroups,
            ]);
    }

    public function update(Request $request, Paper $paper, PaperSection $section)
    {
        $request->validate([
            'questions' => 'required|array'
        ]);

        $questions = [];
        foreach ($request->questions as $id) {
            $questions[$id] = ['paper_id'  =>  $paper->id];
        }

        $section->questions()->sync($questions);

        return redirect()->route('admin.exam.papers.sections.index', [$paper]);
    }
}
