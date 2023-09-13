<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Takshak\Exam\Models\Question;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\QuestionOption;
use Maatwebsite\Excel\Facades\Excel;
use Takshak\Exam\Imports\QuestionsImport;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\QuestionGroup;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::query()
            ->with('questionGroups:id,name')
            ->when($request->get('question'), function ($query) {
                $query->where('question', 'like', "%" . request('question') . "%");
            })
            ->when($request->get('question_group_id'), function ($query) {
                $query->whereHas('questionGroups', function ($query) {
                    $query->where('question_groups.id', request('question_group_id'));
                });
            })
            ->paginate(100);

        return View::first(['admin.exam.questions.index', 'exam::admin.exam.questions.index'])
            ->with([
                'questions'   =>  $questions
            ]);
    }

    public function create()
    {
        $questionGroups = QuestionGroup::get();
        return View::first(['admin.exam.questions.create', 'exam::admin.exam.questions.create'])->with([
            'questionGroups'    =>  $questionGroups
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question'        =>    'required|unique:questions',
            'ques_option'    =>    'required|array',
            'correct_ans'    =>    'required|numeric',
            'answer'        =>    'required|max:500',
            'marks'         =>  'required|numeric|min:1',
            'question_group_id' => 'required|array|min:1'
        ]);

        $ques = Question::create([
            'question'        =>    $request->post('question'),
            'answer'        =>    $request->post('answer'),
            'marks'         =>  $request->post('marks'),
        ]);

        foreach ($request->input('ques_option') as $key => $ques_option) {
            if (!empty($ques_option)) {

                QuestionOption::create([
                    'question_id'    =>    $ques->id,
                    'option_text'    =>    $ques_option,
                    'correct_ans'    => ($key == $request->input('correct_ans')) ? true : false
                ]);
            }
        }

        $ques->questionGroups()->sync($request->question_group_id);

        return redirect()->back()->withErrors('CREATED !! New question is created');
    }

    public function edit($id)
    {
        $question = Question::find($id);
        $questionGroups = QuestionGroup::get();
        return View::first(['admin.exam.questions.edit', 'exam::admin.exam.questions.edit'])->with([
            'question' =>  $question,
            'questionGroups' =>  $questionGroups
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question'        =>    'required|unique:questions,question,' . $question->id,
            'ques_option'    =>    'required|array',
            'correct_ans'    =>    'required|numeric',
            'answer'        =>    'required|max:500',
            'marks'         =>  'required|numeric|min:1',
            'question_group_id' => 'required|array|min:1'
        ]);

        try {
            $question->update([
                'question'        =>    $request->post('question'),
                'answer'        =>    $request->post('answer'),
                'marks'         =>  $request->post('marks'),
            ]);

            QuestionOption::where('question_id', $question->id)->delete();

            foreach ($request->input('ques_option') as $key => $ques_option) {
                if (!empty($ques_option)) {

                    QuestionOption::create([
                        'question_id'    =>    $question->id,
                        'option_text'    =>    $ques_option,
                        'correct_ans'    => ($key == $request->input('correct_ans')) ? true : false
                    ]);
                }
            }

            $question->questionGroups()->sync($request->question_group_id);

            return redirect()->back()->withErrors('UPDATED !! Question is successfully updated');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            Question::where('id', $id)->delete();
            QuestionOption::where('question_id', $id)->delete();
            return redirect()->back()->withErrors('DELETED !! Question is successfully deleted');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function upload()
    {
        return View::first(['admin.exam.questions.upload', 'exam::admin.exam.questions.upload']);
    }

    public function sampleDownload()
    {
        if (Storage::exists('downloadable/questions_import_sample.xlsx')) {
            return Storage::download('downloadable/questions_import_sample.xlsx');
        }

        return response()->download(__DIR__ . '/../../../../../storage/questions_import_sample.xlsx');
    }

    public function uploadDo(Request $request)
    {
        $imported_file = $request->file('upload_file')
            ->storeAs(
                'imports',
                'import-question-' . time() . '.' . $request->file('upload_file')->extension()
            );


        //set_time_limit(0);
        Excel::import(new QuestionsImport(), Storage::path($imported_file));
        return redirect('admin/questions')->withErrors('SUCCESS !! Question List is successfully updated');
        try {
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function htmxList(Request $request)
    {
        $section = null;
        $questionIds = null;
        if ($request->get('section_id')) {
            $section = PaperSection::query()
                ->with('questions:id')
                ->where('id', $request->get('section_id'))
                ->first();
            $questionIds = $section->questions->pluck('id');
        }

        $paper = null;
        if ($request->get('paper_id')) {
            $paper = Paper::query()
                ->with('questions:id')
                ->where('id', $request->get('paper_id'))
                ->first();
            $questionIds = $paper->questions->pluck('id');
        }

        if ($questionIds && $questionIds->count()) {
            $questionIds = $questionIds->map(fn ($item) => "'" . $item . "'")->implode(',');
        }

        $questions = Question::query()
            ->with('questionGroups:id,name')
            ->when($request->get('search'), function ($query) use ($request) {
                $query->where('question', 'LIKE', '%' . $request->search . '%');
            })
            ->when($request->get('question_group_id'), function ($query) {
                $query->whereHas('questionGroups', function ($query) {
                    $query->where('question_groups.id', request('question_group_id'));
                });
            })
            ->when($questionIds, function ($query) use ($questionIds) {
                $query->orderByRaw("FIELD(questions.id, " . $questionIds . ") DESC");
            })
            ->paginate(200)
            ->withQueryString();

        return View::first(['admin.exam.htmx.questions-list', 'exam::admin.exam.htmx.questions-list'])->with([
            'questions' =>  $questions,
            'model'   =>  $section ? $section : $paper,
        ]);
    }
}