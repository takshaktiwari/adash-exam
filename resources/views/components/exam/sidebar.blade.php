
<div id="sidebar" class="bg-dark ">
    <div class="user pt-4 pb-2">
        <div class="text-center">
            <img src="{{ auth()->user()->profileImg() }}" alt="user" class="rounded-circle img-thumbnail mb-3">
            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
            <p class="mb-0 small">{{ auth()->user()->email }}</p>
        </div>
    </div>
    <hr />
    <div class="stats px-3">
        <div class="row gy-2 gx-4">
            <div class="col-6">
                <div class="d-flex">
                    <div class="my-auto me-2">
                        <button type="button" class="btn btn-sm btn-info px-1 py-0">
                            {{ $paper->questions_count }}
                        </button>
                    </div>
                    <div class="my-auto">
                        Questions
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex">
                    <div class="my-auto me-2">
                        <button type="button" class="btn bg-answered px-1 py-0">
                            {{ $answeredCount + $forReviewCount }}
                        </button>
                    </div>
                    <div class="my-auto">
                        Answered
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex">
                    <div class="my-auto me-2">
                        <button type="button" class="btn btn-sm btn-light px-1 py-0">
                            {{ $paper->questions_count - $userQuestions->count() + $markedCount }}
                        </button>
                    </div>
                    <div class="my-auto text-nowrap">
                        Not Answered
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex">
                    <div class="my-auto me-2">
                        <button type="button" class="btn btn-sm bg-marked px-1 py-0">
                            {{ $markedCount }}
                        </button>
                    </div>
                    <div class="my-auto">
                        Marked
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="d-flex">
                    <div class="my-auto me-2">
                        <button type="button" class="btn btn-sm bg-mark_review px-1 py-0">
                            {{ $forReviewCount }}
                        </button>
                    </div>
                    <div class="my-auto">
                        For Review
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr />

    <div class="question_section ">
        @if ($paper->sections_count)
            @foreach ($paper->sections as $section)
                <div class="section mb-2 px-3 border-bottom border-secondary">
                    <a href="" class="list-group-item py-2 d-flex justify-content-between" data-bs-toggle="collapse"
                        data-bs-target="#section_{{ $section->id }}">
                        <span>{{ $section->name }}</span>
                        <span><i class="fa-solid fa-arrow-right"></i></span>
                    </a>

                    <div class="pb-2 {{ !$question->sections->pluck('id')->contains($section->id) ? 'collapse' : '' }}" id="section_{{ $section->id }}">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($section->questions as $sQuestion)
                                <a href="{{ route('exam.paper', [$paper, 'question_id' => $sQuestion->id]) }}"
                                    class="question_item btn btn-sm btn-light bg-{{ $getQuestionClass($sQuestion) }} border">
                                    {{ $loop->iteration }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="d-flex flex-wrap gap-2 px-3">
                @foreach ($paper->questions as $question)
                    <a href="{{ route('exam.paper', [$paper, 'question_id' => $question->id]) }}"
                        class="question_item btn btn-sm btn-light bg-{{ $getQuestionClass($question) }} border">
                        {{ $loop->iteration }}
                    </a>
                @endforeach
            </div>
            <hr />
        @endif
    </div>

    <div class="text-center px-3 mt-4">
        <a href="" class="btn btn-info d-block px-4" data-bs-toggle="modal" data-bs-target="#exam_statistics">
            <i class="fa-solid fa-save"></i> Submit Exam
        </a>
    </div>

</div>
