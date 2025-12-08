<?php

namespace Takshak\Exam\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\QuestionOption;
use Takshak\Exam\Models\UserPaper;
use Takshak\Exam\Models\UserQuestion;

class ExamController extends Controller
{
    public function papers(Request $request)
    {
        $papers = Paper::query()
            ->withCount('questions')
            ->withCount('sections')
            ->withCount(['userPapers' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->withSum('questions', 'marks')
            ->active()
            ->get();

        return View::first(['exam.papers', 'exam::exam.papers'])->with([
            'papers' =>  $papers,
        ]);
    }

    public function authenticate(Request $request, Paper $paper)
    {
        if ($paper->security_code != $request->input('security_code')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Security code is not correct. Please try again.');
        }

        $arr = session('exam', []);
        $arr['authenticated'] = true;
        session(['exam' => $arr]);
        return redirect()->route('exam.instructions', [$paper]);
    }

    public function instructions($paper_id)
    {
        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($paper_id) {
                return Paper::where('id', $paper_id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->firstOrFail();
            }
        );

        if (!$paper->isActive()) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Exam is not activated yet.');
        }
        if ($paper->security_code && !session('exam.authenticated')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Please enter exam security key / code.');
        }

        if (session('exam.paper.id')) {
            return redirect()->route('exam.paper', [session('exam.paper.id')]);
        }

        return View::first(['exam.instructions', 'exam::exam.instructions'])->with([
            'paper' =>  $paper,
        ]);
    }

    public function start(Paper $paper)
    {
        if ($paper->security_code && !session('exam.authenticated')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Please enter exam security key / code.');
        }

        $paper->load(['sections' => function ($query) {
            $query->select('paper_sections.id', 'paper_id');
            $query->with(['questions' => function ($query) {
                $query->whereNull('questions.question_id');
                $query->select('questions.id', 'questions.question_id');
                $query->with('children:id,question_id');
            }]);
        }])
            ->load(['questions' => function ($query) {
                $query->whereNull('questions.question_id');
                $query->select('questions.id', 'questions.question_id');
                $query->with('children:id,question_id');
            }]);


        $userPaper = UserPaper::create([
            'user_id' => auth()->id(),
            'paper_id' => $paper->id,
            'start_at' => now(),
            'end_at' => now()->addMinutes($paper->total_time)
        ]);

        $questionIds = collect([]);
        if ($paper->sections->count()) {
            foreach ($paper->sections as $section) {

                $sectionQuestions = $section->questions;
                if ($paper->shuffle_questions) {
                    $sectionQuestions = $sectionQuestions->shuffle();
                }

                $sectionQuestionIds = collect();
                foreach ($sectionQuestions as $question) {
                    $sectionQuestionIds->push($question->id);
                    if ($question->children->count()) {
                        $sectionQuestionIds->push($question->children->pluck('id'));
                    }
                }

                $questionIds = $questionIds->merge($sectionQuestionIds->flatten());
            }
        } else {
            $questions = $paper->questions;
            if ($paper->shuffle_questions) {
                $questions = $paper->questions->shuffle();
            }

            $questionIds = collect();
            foreach ($questions as $question) {
                $questionIds->push($question->id);
                if ($question->children->count()) {
                    $questionIds->push($question->children->pluck('id'));
                }
            }

            $questionIds = $questionIds->flatten();
        }

        $paper->unsetRelation('sections');
        $paper->unsetRelation('questions');

        $arr = session('exam', []);
        $arr['paper'] = $paper->toArray();
        $arr['user_paper'] = $userPaper->toArray();
        $arr['questions'] = $questionIds->toArray();
        $arr['sections'] = $paper->sections->pluck('id')->toArray();
        $arr['current_section'] = $paper->sections->pluck('id')->first();
        $arr['start_at'] = now()->format('Y-m-d H:i:s');
        $arr['end_at'] = now()->addMinutes($paper->total_time)->format('Y-m-d H:i:s');

        session(['exam' => $arr]);

        return redirect()->route('exam.paper', [$paper]);
    }

    public function paper(Request $request, $paper_id)
    {
        if ($request->get('submit_section')) {
            /**
             * Submitting the sections and going to next question of next section.
             * change current section id in section to next section
             * question will be changed when there is next section and question
             */
            $sectionKey = null;
            foreach (session('exam.sections') as $key => $section) {
                if ($section == $request->get('submit_section')) {
                    $sectionKey = $key;
                    break;
                }
            }

            $questionKey = null;
            foreach (session('exam.questions') as $key => $question) {
                if ($question == $request->get('question_id')) {
                    $questionKey = $key;
                    break;
                }
            }

            if (session('exam.sections.' . $sectionKey + 1) && session('exam.questions.' . $questionKey + 1)) {
                $arr = session('exam', []);
                $arr['current_section'] = session('exam.sections.' . $sectionKey + 1);
                session(['exam' => $arr]);

                return redirect()->route('exam.paper', [
                    $paper_id,
                    'question_id' => session('exam.questions.' . $questionKey + 1)
                ]);
            }
        }

        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($paper_id) {
                return Paper::where('id', $paper_id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->firstOrFail();
            }
        );

        if (!session('exam.paper.id') || !session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }
        if ($paper->security_code && !session('exam.authenticated')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Please enter exam security key / code.');
        }

        $questions = collect(session('exam.questions'));
        $question_id = $request->get('question_id');

        if (!$question_id || !$questions->contains($question_id)) {
            return redirect()->route('exam.paper', [$paper, 'question_id' => $questions->first()]);
        }

        $questionKey = $questions->search(function ($item) use ($question_id) {
            return $question_id == $item;
        });

        $questionsIdsForFilter = $questions->implode(',');

        $userPaper = cache()->remember(
            'user_paper_' . session('exam.user_paper.id'),
            60 * 60 * 6, // for 6 hrs
            function () {
                return UserPaper::query()
                    ->where('id', session('exam.user_paper.id'))
                    ->first();
            }
        );

        if (!$userPaper) {
            return to_route('exam.start', [session('exam.paper.id')])
                ->withErrors('Sorry !! User paper has not created, please start the exam again');
        }

        $paper = cache()->remember(
            'user_paper_paper_' . session('exam.user_paper.id'),
            60 * 60 * 6, // for 6 hrs
            function () use ($paper, $questionsIdsForFilter) {
                return Paper::where('id', $paper->id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->when($paper->sections_count, function ($query) use ($questionsIdsForFilter) {
                        $query->with(['sections' => function ($query) use ($questionsIdsForFilter) {
                            $query->with(['questions' => function ($query) use ($questionsIdsForFilter) {
                                $query->whereNull('questions.question_id');
                                $query->select('questions.id', 'questions.question_id');
                                $query->with('children:id,question_id');
                                $query->orderByRaw("FIELD(questions.id, {$questionsIdsForFilter})");
                            }]);
                        }]);
                    })
                    ->when(!$paper->sections_count, function ($query) use ($questionsIdsForFilter) {
                        $query->with(['questions' => function ($query) use ($questionsIdsForFilter) {
                            $query->whereNull('questions.question_id');
                            $query->select('questions.id', 'questions.question_id');
                            $query->with('children:id,question_id');
                            $query->orderByRaw("FIELD(questions.id, {$questionsIdsForFilter})");
                        }]);
                    })
                    ->first();
            }
        );

        if ($paper?->sections_count && session('exam.paper.lock_sections')) {
            # checking if the current question is in the current section's questions list
            # if not then redirect to last question of current section
            $section = PaperSection::where('id', session('exam.current_section'))
                ->with(['questions' => function ($query) use ($questionsIdsForFilter) {
                    $query->whereNull('questions.question_id');
                    $query->select('questions.id', 'questions.question_id');
                    $query->with('children:id,question_id');
                    $query->orderByRaw("FIELD(questions.id, {$questionsIdsForFilter})");
                }])
                ->first();

            $sectionQuestions = [];
            foreach ($section->questions as $question) {
                $sectionQuestions[] = $question->id;
                foreach ($question->children ?? [] as $child) {
                    $sectionQuestions[] = $child->id;
                }
            }

            if (!in_array($question_id, $sectionQuestions)) {
                return redirect()->route('exam.paper', [$paper, 'question_id' => end($sectionQuestions)]);
            }
        }

        $question = cache()->remember(
            'question_' . $question_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($question_id, $paper) {
                return Question::with('options')
                    ->with('correctOption')
                    ->with(['sections' => function ($query) use ($paper) {
                        $query->where('paper_question_section.paper_id', $paper->id);
                    }])
                    ->with('parent')
                    ->find($question_id);
            }
        );

        $userQuestions = UserQuestion::query()
            ->select('id', 'user_paper_id', 'user_id', 'paper_id', 'question_id', 'user_option_id', 'status')
            ->where('user_paper_id', $userPaper->id)
            ->where('user_id', auth()->id())
            ->where('paper_id', $paper->id)
            ->get();

        $userQuestion = $userQuestions->where('question_id', $question->id)->first();

        return View::first(['exam.paper', 'exam::exam.paper'])->with([
            'paper' =>  $paper,
            'question' =>  $question,
            'questions' =>  $questions,
            'questionKey' =>  $questionKey,
            'userPaper' =>  $userPaper,
            'userQuestion' =>  $userQuestion,
            'userQuestions' =>  $userQuestions,
        ]);
    }

    public function questionSave(Request $request, $paper_id, $question_id)
    {
        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($paper_id) {
                return Paper::where('id', $paper_id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->firstOrFail();
            }
        );

        $question = cache()->remember(
            'question_' . $question_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($question_id) {
                return Question::with('options')
                    ->with('correctOption')
                    ->with('sections')
                    ->find($question_id);
            }
        );

        if (!session('exam.paper.id') || !session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }

        $request->validate([
            'user_option' => 'required|numeric',
            'next_question_id' => 'nullable|numeric'
        ]);

        if (!$question->relationLoaded('options')) {
            $question->with('options');
        }

        $correctOption = $question->options
            ->where('correct_ans', true)
            ->first();

        $userOption = $question->options
            ->where('id', $request->user_option)
            ->first();

        UserQuestion::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'user_paper_id' => session('exam.user_paper.id'),
                'paper_id' => $paper->id,
                'question_id' => $question->id,
            ],
            [
                'user_option_id' => $request->user_option,
                'correct_option_id' => $correctOption?->id,
                'status' => $request->input('mark_review') ? 'mark_review' : 'answered',
                'user_answer_text' => $userOption?->option_text,
                'correct_answer_text' => $correctOption?->option_text,
                'marks' => $question->marks
            ]
        );

        $nexQuestionId = $request->post('next_question_id') ? $request->post('next_question_id') : $question->id;

        return redirect()->route('exam.paper', [$paper, 'question_id' => $nexQuestionId]);
    }

    public function questionMark(Request $request, $paper_id, $question_id)
    {
        if (!session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }

        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($paper_id) {
                return Paper::where('id', $paper_id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->firstOrFail();
            }
        );

        $question = cache()->remember(
            'question_' . $question_id,
            60 * 60 * 6, // for 6 hrs
            function () use ($question_id) {
                return Question::with('options')
                    ->with('correctOption')
                    ->with('sections')
                    ->find($question_id);
            }
        );

        UserQuestion::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'paper_id' => $paper->id,
                'user_paper_id' => session('exam.user_paper.id'),
                'question_id' => $question->id,
            ],
            [
                'user_option_id' => null,
                'correct_option_id' => null,
                'status' => 'marked',
                'user_answer_text' => null,
                'correct_answer_text' => null,
                'marks' => $question->marks
            ]
        );

        $nexQuestionId = $request->input('next_question_id') ? $request->input('next_question_id') : $question->id;

        return redirect()->route('exam.paper', [$paper, 'question_id' => $nexQuestionId]);
    }

    public function questionReset($paper_id, $question_id)
    {
        UserQuestion::query()
            ->where([
                'user_id' => auth()->id(),
                'paper_id' => $paper_id,
                'user_paper_id' => session('exam.user_paper.id'),
                'question_id' => $question_id,
            ])
            ->delete();

        return redirect()->route('exam.paper', [$paper_id, 'question_id' => $question_id]);
    }

    public function submit(Paper $paper)
    {
        if (!session('exam.paper.id') || !session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }
        if ($paper->security_code && !session('exam.authenticated')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Please enter exam security key / code.');
        }

        $userPaper = UserPaper::find(session('exam.user_paper.id'));
        $userPaper->update(['submit_at' => now()]);

        UserQuestion::query()
            ->where('user_paper_id', $userPaper->id)
            ->where('status', 'mark_review')
            ->update(['status' => 'answered']);

        $userQuestions = UserQuestion::query()
            ->where('user_paper_id', $userPaper->id)
            ->get();

        foreach ($userQuestions as $userQuestion) {
            if ($userQuestion->status == 'answered') {
                if ($userQuestion->user_option_id != $userQuestion->correct_option_id && $paper->minus_mark_percent) {
                    $userQuestion->marks = ($userQuestion->marks * ($paper->minus_mark_percent / 100)) * (-1);
                } elseif ($userQuestion->user_option_id != $userQuestion->correct_option_id) {
                    $userQuestion->marks = 0;
                }
            } else {
                $userQuestion->marks = 0;
            }

            $userQuestion->save();
        }


        request()->session()->forget('exam');

        return redirect()->route('exam.papers')->withSuccess('SUCCESS !! Exam has been submitted successfully.');
    }
}
