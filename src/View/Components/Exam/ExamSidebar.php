<?php

namespace Takshak\Exam\View\Components\Exam;

use Illuminate\View\Component;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\Question;

class ExamSidebar extends Component
{
    public $answeredCount;
    public $answeredIds;
    public $markedCount;
    public $markedIds;
    public $forReviewCount;
    public $sections = [];

    /**
     * Pre-built lookup map (question_id => status) for O(1) access in
     * getQuestionClass() instead of an O(n) collection search per question.
     */
    protected array $questionStatusMap = [];

    public function __construct(public $paper, public $userQuestions, public $question)
    {
        $this->answeredIds   = $this->userQuestions->where('status', 'answered')->pluck('question_id');
        $this->answeredCount = $this->userQuestions->where('status', 'answered')->count();
        $this->markedIds     = $this->userQuestions->where('status', 'marked')->pluck('question_id');
        $this->markedCount   = $this->userQuestions->where('status', 'marked')->count();
        $this->forReviewCount = $this->userQuestions->where('status', 'mark_review')->count();

        // Build the O(1) lookup map once at construction time.
        foreach ($this->userQuestions as $uq) {
            $this->questionStatusMap[$uq->question_id] = $uq->status;
        }

        $sectionsCount = isset($this->paper->sections_count)
            ? $this->paper->sections_count
            : $this->paper?->sections?->count();

        if ($sectionsCount) {
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

    /**
     * O(1) map lookup instead of an O(n) collection search per question.
     * Returns CSS class name for sidebar question status badge.
     */
    public function getQuestionClass($questionId): string
    {
        $questionId = $questionId instanceof Question ? $questionId->id : $questionId;

        return $this->questionStatusMap[$questionId] ?? 'not-answered';
    }

    public function render()
    {
        return View::first([
            'components.exam.sidebar',
            'exam::components.exam.sidebar',
        ]);
    }
}
