<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\UserQuestion;

class ExamSidebar extends Component
{
    public $answeredCount;
    public $answeredIds;
    public $markedCount;
    public $markedIds;
    public $forReviewCount;
    public $sections = [];

    public function __construct(public $paper, public $userQuestions, public $question)
    {
        $this->answeredIds = $this->userQuestions->where('status', 'answered')->pluck('id');
        $this->answeredCount = $this->userQuestions->where('status', 'answered')->count();
        $this->markedIds = $this->userQuestions->where('status', 'marked')->pluck('id');
        $this->markedCount = $this->userQuestions->where('status', 'marked')->count();
        $this->forReviewCount = $this->userQuestions->where('status', 'mark_review')->count();

        $sestionsCount = isset($this->paper->sections_count)
            ? $this->paper->sections_count
            : $this->paper?->sections?->count();

        if($sestionsCount) {
            $this->sections = $this->paper->sections->map(function ($section) {
                $questions = [];
                foreach ($section->questions as $question) {
                    $questions[] = $question->id;
                    foreach ($question->children ?? [] as $child) {
                        $questions[] = $child->id;
                    }
                }

                unset($section->questions);
                $section->questions = $questions;

                return $section;
            });
        }
    }

    public function getQuestionClass($questionId)
    {
        $questionId = $questionId instanceof Question ? $questionId->id : $questionId;
        $userQuestion = $this->userQuestions->where('question_id', $questionId)->first();

        if (!$userQuestion) {
            return 'not-answered';
        }

        return $userQuestion->status;
    }

    public function render()
    {
        return View::first([
            'components.exam.sidebar',
            'exam::components.exam.sidebar'
        ]);
    }
}
