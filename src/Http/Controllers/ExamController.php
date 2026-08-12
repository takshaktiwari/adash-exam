<?php

namespace Takshak\Exam\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Takshak\Exam\Models\Paper;
use Illuminate\Support\Facades\View;
use Takshak\Exam\Models\PaperSection;
use Takshak\Exam\Models\Question;
use Takshak\Exam\Models\UserPaper;
use Takshak\Exam\Models\UserQuestion;
use Takshak\Exam\Services\ExamScoringService;

class ExamController extends Controller
{
    public function __construct(private ExamScoringService $examScoring)
    {
    }

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
            'papers' => $papers,
        ]);
    }

    public function authenticate(Request $request, Paper $paper)
    {
        if ($paper->security_code != $request->input('security_code')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! Security code is not correct. Please try again.');
        }

        $arr = session('exam.paper.id') == $paper->id ? session('exam', []) : [];
        $arr['authenticated'] = true;
        session(['exam' => $arr]);
        return redirect()->route('exam.instructions', [$paper]);
    }

    public function instructions($paper_id)
    {
        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6,
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
            'paper' => $paper,
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
            'user_id'  => auth()->id(),
            'paper_id' => $paper->id,
            'start_at' => now(),
            'end_at'   => now()->addMinutes($paper->total_time),
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

        // Section/current-section ids must be read before the relations below are
        // unset, or they would come back empty.
        $sectionIds       = $paper->sections->pluck('id')->toArray();
        $currentSectionId = $paper->sections->pluck('id')->first();

        $paper->unsetRelation('sections');
        $paper->unsetRelation('questions');

        $arr                     = session('exam', []);
        $arr['paper']            = $paper->toArray();
        $arr['user_paper']       = $userPaper->toArray();
        $arr['questions']        = $questionIds->toArray();
        $arr['sections']         = $sectionIds;
        $arr['current_section']  = $currentSectionId;
        $arr['start_at']         = now()->format('Y-m-d H:i:s');
        $arr['end_at']           = now()->addMinutes($paper->total_time)->format('Y-m-d H:i:s');

        session(['exam' => $arr]);

        return redirect()->route('exam.paper', [$paper]);
    }

    public function paper(Request $request, $paper_id)
    {
        if ($request->get('submit_section')) {
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
                $arr                   = session('exam', []);
                $arr['current_section'] = session('exam.sections.' . $sectionKey + 1);
                session(['exam' => $arr]);

                return redirect()->route('exam.paper', [
                    $paper_id,
                    'question_id' => session('exam.questions.' . $questionKey + 1),
                ]);
            }
        }

        // Structural paper data (sections/questions counts, etc.) is shared across
        // every student sitting this paper, so it is cached once per paper, not
        // per attempt.
        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6,
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

        $questions   = collect(session('exam.questions'));
        $question_id = $request->get('question_id');

        if (!$question_id || !$questions->contains($question_id)) {
            return redirect()->route('exam.paper', [$paper, 'question_id' => $questions->first()]);
        }

        $questionKey           = $questions->search(fn($item) => $question_id == $item);
        $questionsIdsForFilter = $questions->implode(',');

        // UserPaper is a single row read on every question navigation — a simple
        // primary key lookup is already fast, and caching it risks a stale
        // submit_at surviving a re-attempt.
        $userPaper = UserPaper::find(session('exam.user_paper.id'));

        if (!$userPaper) {
            return to_route('exam.start', [session('exam.paper.id')])
                ->withErrors('Sorry !! User paper has not created, please start the exam again');
        }

        // The structural (DB-ordered) question tree is cached once per paper and
        // shared across students; this user's shuffled order lives only in their
        // session, so no per-user cache is needed here.
        $paperWithQuestions = cache()->remember(
            'paper_questions_' . $paper_id,
            60 * 60 * 6,
            function () use ($paper) {
                return Paper::where('id', $paper->id)
                    ->withCount('sections')
                    ->withCount('questions')
                    ->withSum('questions', 'marks')
                    ->when($paper->sections_count, function ($query) {
                        $query->with(['sections' => function ($query) {
                            $query->with(['questions' => function ($query) {
                                $query->whereNull('questions.question_id');
                                $query->select('questions.id', 'questions.question_id');
                                $query->with('children:id,question_id');
                                // No FIELD() ordering here — ordering is per-user in session.
                            }]);
                        }]);
                    })
                    ->when(!$paper->sections_count, function ($query) {
                        $query->with(['questions' => function ($query) {
                            $query->whereNull('questions.question_id');
                            $query->select('questions.id', 'questions.question_id');
                            $query->with('children:id,question_id');
                        }]);
                    })
                    ->first();
            }
        );

        // Apply THIS user's session (shuffled) ordering to the shared, DB-ordered cache.
        // The structural cache MUST stay in DB order because it is shared across every
        // student; here we re-sort the parent questions in PHP by their position in
        // session('exam.questions') so the sidebar numbering matches Prev/Next navigation
        // when shuffle_questions is enabled. Eager-loaded children ride along with their
        // parent. On a cache hit the object is freshly unserialized per request, so this
        // mutation is request-local and never leaks into the shared cache.
        if ($paperWithQuestions) {
            $paper = $paperWithQuestions;

            $sessionOrder   = array_flip(session('exam.questions', []));
            $orderBySession = function ($question) use ($sessionOrder) {
                return $sessionOrder[$question->id] ?? PHP_INT_MAX;
            };

            if ($paper->sections_count) {
                $paper->sections->each(function ($section) use ($orderBySession) {
                    $section->setRelation(
                        'questions',
                        $section->questions->sortBy($orderBySession)->values()
                    );
                });
            } else {
                $paper->setRelation(
                    'questions',
                    $paper->questions->sortBy($orderBySession)->values()
                );
            }
        }

        // Section structure doesn't change mid-exam, so the section's question SET is
        // cached keyed by section id only — membership is order-independent, so this
        // stays safely shareable across users. The redirect target (last question of
        // the section) is derived from THIS user's session order, so it stays correct
        // under shuffle_questions.
        if ($paper?->sections_count && session('exam.paper.lock_sections')) {
            $currentSectionId = session('exam.current_section');
            $section          = cache()->remember(
                'section_questions_' . $currentSectionId,
                60 * 60 * 6,
                function () use ($currentSectionId) {
                    return PaperSection::where('id', $currentSectionId)
                        ->with(['questions' => function ($query) {
                            $query->whereNull('questions.question_id');
                            $query->select('questions.id', 'questions.question_id');
                            $query->with('children:id,question_id');
                        }])
                        ->first();
                }
            );

            $sectionQuestions = [];
            foreach ($section->questions as $question) {
                $sectionQuestions[] = $question->id;
                foreach ($question->children ?? [] as $child) {
                    $sectionQuestions[] = $child->id;
                }
            }

            if (!in_array($question_id, $sectionQuestions)) {
                // Last question of this section in the USER's (session) order.
                $sessionOrdered = array_values(
                    array_intersect(session('exam.questions', []), $sectionQuestions)
                );
                $target = end($sessionOrdered) ?: end($sectionQuestions);
                return redirect()->route('exam.paper', [$paper, 'question_id' => $target]);
            }
        }

        // correctOption is deliberately not eager-loaded/cached here: this object is
        // sent to the student's view, and shipping the correct answer to the client
        // before they submit is both a security leak and unnecessary for rendering.
        $question = cache()->remember(
            'question_' . $question_id,
            60 * 60 * 6,
            function () use ($question_id, $paper) {
                return Question::with('options')
                    ->with(['sections' => function ($query) use ($paper) {
                        $query->where('paper_question_section.paper_id', $paper->id);
                    }])
                    ->with('parent')
                    ->find($question_id);
            }
        );

        $userQuestions = $this->getUserQuestions($userPaper->id);

        $userQuestion = $userQuestions->where('question_id', $question->id)->first();

        return View::first(['exam.paper', 'exam::exam.paper'])->with([
            'paper'        => $paper,
            'question'     => $question,
            'questions'    => $questions,
            'questionKey'  => $questionKey,
            'userPaper'    => $userPaper,
            'userQuestion' => $userQuestion,
            'userQuestions' => $userQuestions,
        ]);
    }

    public function questionSave(Request $request, $paper_id, $question_id)
    {
        if (!session('exam.paper.id') || !session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }

        // Only the paper's id/marks are needed here (redirect target), so this stays
        // on the lightweight cached paper rather than loading its full question tree.
        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6,
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
            60 * 60 * 6,
            function () use ($question_id, $paper) {
                return Question::with('options')
                    ->with(['sections' => function ($query) use ($paper) {
                        $query->where('paper_question_section.paper_id', $paper->id);
                    }])
                    ->with('parent')
                    ->find($question_id);
            }
        );

        $request->validate([
            'user_option'    => 'required|numeric',
            'next_question_id' => 'nullable|numeric',
        ]);

        $correctOption = $question->options->where('correct_ans', true)->first();
        $userOption    = $question->options->where('id', $request->user_option)->first();

        UserQuestion::updateOrCreate(
            [
                'user_id'      => auth()->id(),
                'user_paper_id' => session('exam.user_paper.id'),
                'paper_id'     => $paper->id,
                'question_id'  => $question->id,
            ],
            [
                'user_option_id'    => $request->user_option,
                'correct_option_id' => $correctOption?->id,
                'status'            => $request->input('mark_review') ? 'mark_review' : 'answered',
                'user_answer_text'  => $userOption?->option_text,
                'correct_answer_text' => $correctOption?->option_text,
                'marks'             => $question->marks,
            ]
        );

        $this->invalidateUserQuestionsCache(session('exam.user_paper.id'));

        $nextQuestionId = $request->post('next_question_id') ?: $question->id;

        return redirect()->route('exam.paper', [$paper, 'question_id' => $nextQuestionId]);
    }

    public function questionMark(Request $request, $paper_id, $question_id)
    {
        if (!session('exam.user_paper.id')) {
            return redirect()->route('exam.papers')->withErrors('SORRY !! You need to start the exam again');
        }

        $paper = cache()->remember(
            'paper_' . $paper_id,
            60 * 60 * 6,
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
            60 * 60 * 6,
            function () use ($question_id, $paper) {
                return Question::with('options')
                    ->with(['sections' => function ($query) use ($paper) {
                        $query->where('paper_question_section.paper_id', $paper->id);
                    }])
                    ->with('parent')
                    ->find($question_id);
            }
        );

        UserQuestion::updateOrCreate(
            [
                'user_id'      => auth()->id(),
                'paper_id'     => $paper->id,
                'user_paper_id' => session('exam.user_paper.id'),
                'question_id'  => $question->id,
            ],
            [
                'user_option_id'    => null,
                'correct_option_id' => null,
                'status'            => 'marked',
                'user_answer_text'  => null,
                'correct_answer_text' => null,
                'marks'             => $question->marks,
            ]
        );

        $this->invalidateUserQuestionsCache(session('exam.user_paper.id'));

        $nextQuestionId = $request->input('next_question_id') ?: $question->id;

        return redirect()->route('exam.paper', [$paper, 'question_id' => $nextQuestionId]);
    }

    public function questionReset($paper_id, $question_id)
    {
        UserQuestion::query()
            ->where([
                'user_id'      => auth()->id(),
                'paper_id'     => $paper_id,
                'user_paper_id' => session('exam.user_paper.id'),
                'question_id'  => $question_id,
            ])
            ->delete();

        $this->invalidateUserQuestionsCache(session('exam.user_paper.id'));

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

        $userPaperId = session('exam.user_paper.id');
        $userPaper   = UserPaper::find($userPaperId);
        $userPaper->update(['submit_at' => now()]);

        // Scoring lives in ExamScoringService so this path and the scheduled finalizer
        // for expired-but-never-submitted attempts (exam:finalize-expired-user-papers)
        // share one implementation and cannot drift apart.
        $this->examScoring->finalizeUserPaper($userPaper->id);

        $this->invalidateUserQuestionsCache($userPaperId);

        request()->session()->forget('exam');

        return redirect()->route('exam.papers')->withSuccess('SUCCESS !! Exam has been submitted successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // Private helpers for UserQuestion caching
    // ─────────────────────────────────────────────────────────────

    /**
     * Get cached UserQuestion collection for the current user paper.
     * TTL is short (30 seconds) to ensure sidebar stays accurate
     * while reducing DB hits on rapid question navigation.
     */
    private function getUserQuestions(int $userPaperId)
    {
        return cache()->remember(
            'user_questions_' . $userPaperId,
            30, // 30 seconds — short TTL, invalidated on every write action
            function () use ($userPaperId) {
                return UserQuestion::query()
                    ->select('id', 'user_paper_id', 'user_id', 'paper_id', 'question_id', 'user_option_id', 'status')
                    ->where('user_paper_id', $userPaperId)
                    ->where('user_id', auth()->id())
                    ->get();
            }
        );
    }

    /**
     * Invalidate the UserQuestion cache for a given user paper.
     * Called after every write operation (save, mark, reset, submit).
     */
    private function invalidateUserQuestionsCache(int $userPaperId): void
    {
        cache()->forget('user_questions_' . $userPaperId);
    }
}
