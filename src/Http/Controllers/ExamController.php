<?php

namespace Takshak\Exam\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;
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

    public function instructions(Paper $paper)
    {
        if (!$paper->isActive()) {
            return redirect('/')->withErrors('SORRY !! Exam is not activated yet.');
        }
        $paper->loadCount('sections')->loadCount('questions')->loadSum('questions', 'marks');
        return View::first(['exam.instructions', 'exam::exam.instructions'])->with([
            'paper' =>  $paper,
        ]);
    }

    public function start(Paper $paper)
    {
        $userPaper = UserPaper::create([
            'user_id' => auth()->id(),
            'paper_id' => $paper->id,
            'start_at' => now(),
            'end_at' => now()->addMinutes($paper->total_time)
        ]);

        session([
            'exam' => [
                'paper' => $paper,
                'user_paper' => $userPaper,
                'questions' => $paper->questions->pluck('id')
            ]
        ]);

        return redirect()->route('exam.paper', [$paper]);
    }

    public function paper(Request $request, Paper $paper)
    {
        if (!session('exam.paper.id') || !session('exam.user_paper.id')) {
            return redirect('/')->withErrors('SORRY !! You need to start the exam again');
        }
        $questions = collect(session('exam.questions'));
        $question_id = $request->get('question_id');

        if (!$question_id || !$questions->contains($question_id)) {
            return redirect()->route('exam.paper', [$paper, 'question_id' => $questions->first()]);
        }

        $questionKey = $questions->search(function ($item) use ($question_id) {
            return $question_id == $item;
        });

        $paper->load('sections.questions')
            ->loadCount('sections')
            ->loadCount('questions')
            ->loadSum('questions', 'marks');

        if ($paper->sections_count) {
            $paper->load('sections.questions');
        } else {
            $paper->load('questions');
        }

        $question = Question::with('options')->with('sections')->find($question_id);

        $userPaper = UserPaper::query()
            ->where('id', session('exam.user_paper.id'))
            ->first();

        $userQuestion = UserQuestion::query()
            ->where('user_paper_id', $userPaper->id)
            ->where('user_id', auth()->id())
            ->where('paper_id', $paper->id)
            ->where('question_id', $question->id)
            ->first();

        $userQuestions = UserQuestion::query()
            ->where('user_paper_id', $userPaper->id)
            ->where('user_id', auth()->id())
            ->where('paper_id', $paper->id)
            ->get();

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

    public function questionSave(Request $request, Paper $paper, Question $question)
    {
        $request->validate([
            'user_option' => 'required|numeric',
            'next_question_id' => 'nullable|numeric'
        ]);

        $question->load('options');
        $correctOption = $question->options->where('correct_ans', true)->first();

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
                'user_answer_text' => QuestionOption::find($request->user_option)?->option_text,
                'correct_answer_text' => $correctOption?->option_text,
                'marks' => $question->marks
            ]
        );

        $nexQuestionId = $request->post('next_question_id') ? $request->post('next_question_id') : $question->id;

        return redirect()->route('exam.paper', [$paper, 'question_id' => $nexQuestionId]);
    }

    public function questionMark(Request $request, Paper $paper, Question $question)
    {
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

    public function submit(Paper $paper)
    {
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
            if($userQuestion->status == 'answered'){
                if ($userQuestion->user_option_id != $userQuestion->correct_option_id && $paper->minus_mark_percent) {
                    $userQuestion->marks = ($userQuestion->marks * ($paper->minus_mark_percent / 100)) * (-1);
                } elseif ($userQuestion->user_option_id != $userQuestion->correct_option_id) {
                    $userQuestion->marks = 0;
                }
            }else{
                $userQuestion->marks = 0;
            }

            $userQuestion->save();
        }


        request()->session()->forget('exam');

        return redirect()->route('exam.papers')->withSuccess('SUCCESS !! Exam has been submitted successfully.');
    }
}