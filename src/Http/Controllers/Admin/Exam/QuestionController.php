<?php

namespace Takshak\Exam\Http\Controllers\Admin\Exam;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Takshak\Exam\Models\Question;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\QuestionOption;
use Maatwebsite\Excel\Facades\Excel;
use Takshak\Exam\Exports\QuestionsExport;
use Takshak\Exam\Imports\QuestionsImport;
use Takshak\Exam\Models\Paper;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\QuestionGroup;
use Takshak\Imager\Facades\Imager;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::query()
            ->withCount('children')
            ->with('questionGroups:id,name')
            ->with('parent:id,question')
            ->with('papers:id,title')
            ->when($request->get('question_id'), function ($query) use ($request) {
                $query->where('question_id', $request->get('question_id'));
            })
            ->when(!$request->get('question_id'), function ($query) use ($request) {
                $query->parent();
            })
            ->when($request->get('question'), function ($query) {
                $query->where('question', 'like', "%" . request('question') . "%");
            })
            ->when($request->get('question_group_id'), function ($query) {
                $query->whereHas('questionGroups', function ($query) {
                    $query->where('question_groups.id', request('question_group_id'));
                });
            })
            ->latest()
            ->paginate(100);

        $questionGroups = collect([]);
        if ($request->get('filter')) {
            $questionGroups = QuestionGroup::select('id', 'name')->get();
        }

        return View::first(['admin.exam.questions.index', 'exam::admin.exam.questions.index'])
            ->with([
                'questions'   =>  $questions,
                'questionGroups' => $questionGroups
            ]);
    }

    public function create()
    {
        $questions = Question::select('id', 'question')->parent()->get();
        $questionGroups = QuestionGroup::get();
        $parentQuestion = null;
        if (request('question_id')) {
            $parentQuestion = Question::with('questionGroups')->find(request('question_id'));
        }
        return View::first(['admin.exam.questions.create', 'exam::admin.exam.questions.create'])->with([
            'questionGroups'    =>  $questionGroups,
            'questions'    =>  $questions,
            'parentQuestion'    =>  $parentQuestion
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_id'        =>    'nullable|numeric',
            'question'        =>    'required|unique:questions',
            'ques_option'    =>    'required|array',
            'correct_ans'    =>    'required|numeric',
            'answer'        =>    'required|max:500',
            'marks'         =>  'required|numeric|min:1',
            'question_group_id' => 'required|array|min:1',
            'image' => 'nullable|file'
        ]);

        $ques = new Question();
        $ques->question_id = $request->post('question_id');
        $ques->question = $request->post('question');
        $ques->context = $request->post('context');
        $ques->answer   = $request->post('answer');
        $ques->marks    = $request->post('marks');

        if ($request->file('image')) {
            $ques->image = str()->of(microtime())->slug('-')
                ->prepend('questions/')
                ->append('.' . $request->file('image')->extension());
            Imager::init($request->file('image'))
                ->resizeWidth(400)
                ->save(Storage::disk('public')->path($ques->image));
        }

        $ques->save();

        foreach ($request->input('ques_option') as $key => $ques_option) {
            if (empty($ques_option) && empty($request->file('ques_option_img')[$key])) {
                continue;
            }

            $questionOption = new QuestionOption();
            $questionOption->question_id = $ques->id;
            $questionOption->option_text = $ques_option;
            $questionOption->correct_ans = ($key == $request->input('correct_ans')) ? true : false;

            if (!empty($request->file('ques_option_img')[$key])) {
                $questionOption->option_img = str()->of(microtime())->slug('-')
                    ->prepend('options/')
                    ->append('.')
                    ->append($request->file('ques_option_img')[$key]->extension());

                Imager::init($request->file('ques_option_img')[$key])
                    ->resizeWidth(400)
                    ->save(Storage::disk('public')->path($questionOption->option_img))
                    ->destroy();
            }

            $questionOption->save();
        }

        $ques->questionGroups()->sync($request->question_group_id);

        return to_route('admin.exam.questions.create')->withErrors('CREATED !! New question is created');
    }

    public function show(Question $question)
    {
        $question->load('parent');
        return View::first(['admin.exam.questions.show', 'exam::admin.exam.questions.show'])
            ->with([
                'question'   =>  $question
            ]);
    }

    public function edit($id)
    {
        cache()->forget('question_' . $id);
        $question = Question::find($id);
        $questions = Question::select('id', 'question')->get();
        $questionGroups = QuestionGroup::get();
        return View::first(['admin.exam.questions.edit', 'exam::admin.exam.questions.edit'])->with([
            'question' =>  $question,
            'questions' =>  $questions,
            'questionGroups' =>  $questionGroups
        ]);
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_id' => 'nullable|numeric',
            'question'        =>    'required|unique:questions,question,' . $question->id,
            'ques_option'    =>    'required|array',
            'correct_ans'    =>    'required|numeric',
            'answer'        =>    'required|max:500',
            'marks'         =>  'required|numeric|min:1',
            'question_group_id' => 'required|array|min:1'
        ]);

        $question->question_id = $request->post('question_id');
        $question->question = $request->post('question');
        $question->context = $request->post('context');
        $question->answer   = $request->post('answer');
        $question->marks    = $request->post('marks');

        if ($request->file('image')) {
            $question->image = str()->of(microtime())->slug('-')
                ->prepend('questions/')
                ->append('.' . $request->file('image')->extension());
            Imager::init($request->file('image'))
                ->resizeWidth(400)
                ->save(Storage::disk('public')->path($question->image));
        }
        $question->save();


        QuestionOption::where('question_id', $question->id)->delete();

        foreach ($request->input('ques_option') as $key => $ques_option) {
            if (empty($ques_option) && empty($request->file('ques_option_img')[$key]) && empty($request->input('option_imgs')[$key])) {
                continue;
            }

            $questionOption = new QuestionOption();
            $questionOption->question_id = $question->id;
            $questionOption->option_text = $ques_option;
            $questionOption->correct_ans = ($key == $request->input('correct_ans')) ? true : false;

            if (!empty($request->input('option_imgs')[$key])) {
                $questionOption->option_img = $request->input('option_imgs')[$key];
            } elseif (!empty($request->file('ques_option_img')[$key])) {
                $questionOption->option_img = str()->of(microtime())->slug('-')
                    ->prepend('options/')
                    ->append('.')
                    ->append($request->file('ques_option_img')[$key]->extension());

                Imager::init($request->file('ques_option_img')[$key])
                    ->resizeWidth(400)
                    ->save(Storage::disk('public')->path($questionOption->option_img))
                    ->destroy();
            }

            $questionOption->save();
        }

        $question->questionGroups()->sync($request->question_group_id);
        cache()->forget('question_' . $question->id);

        return redirect()->back()->withErrors('UPDATED !! Question is successfully updated');
    }

    public function destroy($id)
    {
        try {
            cache()->forget('question_' . $id);
            Question::where('id', $id)->delete();
            QuestionOption::where('question_id', $id)->delete();
            return redirect()->back()->withErrors('DELETED !! Question is successfully deleted');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    public function upload()
    {
        $questionGroups = QuestionGroup::pluck('name');
        return View::first(['admin.exam.questions.upload', 'exam::admin.exam.questions.upload'])->with([
            'questionGroups'    =>  $questionGroups
        ]);
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


        try {
            Excel::import(new QuestionsImport(), Storage::path($imported_file));
            return redirect()->route('admin.exam.questions.index')->withErrors('SUCCESS !! Question List is successfully updated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function htmxList(Request $request)
    {
        $section = null;
        $questionIds = null;
        $url_param_name = $request->get('section_id') ? 'section_id' : 'paper_id';
        $url_param_value = $request->get('section_id') ? $request->get('section_id') : $request->get('paper_id');

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
        } else {
            $questionIds = null;
        }

        $questions = Question::query()
            ->withCount('children')
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
            ->when($questionIds, function ($query) use ($questionIds) {
                $query->orderByRaw("FIELD(questions.id, " . $questionIds . ") DESC");
            })
            ->when(request('not_used'), function ($query) use ($questionIds) {
                $query->doesntHave('papers');
            })
            ->parent()
            ->latest()
            ->paginate(200)
            ->withQueryString();

        return View::first(['admin.exam.htmx.questions-list', 'exam::admin.exam.htmx.questions-list'])->with([
            'questions' =>  $questions,
            'model'   =>  $section ? $section : $paper,
            'url_param_name' => $url_param_name,
            'url_param_value' => $url_param_value,
        ]);
    }

    public function htmxAttachToggle(Request $request)
    {
        $request->validate([
            'question_id' => 'required|numeric',
            'section_id' => 'nullable|numeric',
            'paper_id' => 'nullable|numeric'
        ]);

        $question = Question::select('id')->with('children:id,question_id')->find($request->get('question_id'));
        $questionIds = collect($question->id)->merge($question->children->pluck('id'));

        if ($request->get('section_id')) {
            $model = PaperSection::with('questions:id')->find($request->get('section_id'));

            if ($model->questions->pluck('id')->contains($question->id)) {
                foreach ($questionIds as $questionId) {
                    $model->questions()->detach($questionId);
                }
            } else {
                foreach ($questionIds as $questionId) {
                    $model->questions()->attach([$questionId => ['paper_id' => $model->paper_id]]);
                }
            }
        } else {
            $model = Paper::with('questions:id')->find($request->get('paper_id'));

            if ($model->questions->pluck('id')->contains($question->id)) {
                foreach ($questionIds as $questionId) {
                    $model->questions()->detach($questionId);
                }
            } else {
                foreach ($questionIds as $questionId) {
                    $model->questions()->attach($questionId);
                }
            }
        }

        $model->loadCount('questions');
        return $model->questions_count;
    }

    public function htmxBindList(Request $request)
    {
        $parentQuestion = Question::with('children')->find($request->get('question_id'));
        $questionIds = Question::where('question_id', $request->get('question_id'))->pluck('id');
        $childrenQuestionIds = $questionIds;

        if ($questionIds && $questionIds->count()) {
            $questionIds = $questionIds->map(fn ($item) => "'" . $item . "'")->implode(',');
        } else {
            $questionIds = null;
        }

        $questions = Question::query()
            ->withCount('children')
            ->with('papers:id,title')
            ->with('parent:id,question')
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
            ->latest()
            ->paginate(100)
            ->withQueryString();

        return View::first(['admin.exam.htmx.questions-bind-list', 'exam::admin.exam.htmx.questions-bind-list'])->with([
            'questions' =>  $questions,
            'childrenQuestionIds' =>  $childrenQuestionIds,
            'parentQuestion' => $parentQuestion
        ]);
    }

    public function htmxBindToggle(Request $request)
    {
        $request->validate([
            'question_id' => 'required|numeric',
            'parent_question_id' => 'required|numeric',
        ]);

        Question::where('id', $request->get('question_id'))->update([
            'question_id' => $request->parent_question_id
        ]);

        return true;
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'question_ids' => 'required'
        ]);

        $question_ids = explode(',', $request->question_ids);
        if (count($question_ids)) {
            Question::whereIn('id', $question_ids)->delete();
        }

        return redirect()->route('admin.exam.questions.index')->withSuccess('SUCCESS !! Selected questions are successfully deleted');
    }

    public function export()
    {
        return (new QuestionsExport())->download('questions.xlsx');
    }

    public function bind(Question $question)
    {
        $question->load('children');
        $questionGroups = QuestionGroup::select('id', 'name')->get();
        return View::first(['admin.exam.questions.bind', 'exam::admin.exam.questions.bind'])->with([
            'question' =>  $question,
            'questionGroups' =>  $questionGroups,
        ]);
    }

    public function ajaxShow(Question $question)
    {
        return $question->load('questionGroups');
    }
}
