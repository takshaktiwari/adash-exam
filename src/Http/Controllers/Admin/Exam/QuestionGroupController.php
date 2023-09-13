<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\QuestionGroup;

class QuestionGroupController extends Controller
{
    public function index(Request $request)
    {
        $questionGroups = QuestionGroup::withCount('questions')->paginate(50);
        return View::first(['admin.exam.question-groups.index', 'exam::admin.exam.question-groups.index'])->with([
            'questionGroups'    =>  $questionGroups
        ]);
    }

    public function create()
    {
        return View::first(['admin.exam.question-groups.create', 'exam::admin.exam.question-groups.create']);
    }

    public function store(Request $request)
    {
        $questionGroup = new QuestionGroup();
        $questionGroup->name = $request->name;
        $questionGroup->save();

        return redirect()->route('admin.exam.question-groups.index')->withSuccess('SUCCESS !! Group name has been created');
    }

    public function edit(QuestionGroup $questionGroup)
    {
        return View::first(['admin.exam.question-groups.edit', 'exam::admin.exam.question-groups.edit'])->with([
            'questionGroup'    =>  $questionGroup
        ]);
    }

    public function update(Request $request, QuestionGroup $questionGroup)
    {
        $questionGroup->name = $request->name;
        $questionGroup->save();

        return redirect()->route('admin.exam.question-groups.index')->withSuccess('SUCCESS !! Group name has been updated');
    }

    public function destroy(Request $request, QuestionGroup $questionGroup)
    {
        $questionGroup->delete();

        return redirect()->route('admin.exam.question-groups.index')->withSuccess('SUCCESS !! Group name has been deleted');
    }
}
