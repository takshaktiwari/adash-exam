@foreach ($questions as $question)
    @if ($question->children_count)
        @continue
    @endif

    <div class="form-check mb-0 ps-0">
        <label class="form-check-label list-group-item mb-0 " style="padding-left: 2rem;">
            <input
                hx-get="{{ route('admin.exam.htmx.questions.bind.toggle', ['question_id' => $question->id, 'parent_question_id' => $parentQuestion->id]) }}"
                hx-trigger="change" hx-swap="none" class="form-check-input" type="checkbox" name="questions[]"
                value="{{ $question->id }}" {{ $childrenQuestionIds->contains($question->id) ? 'checked' : '' }}>

            <span>
                @if ($question->question_id)
                    <span data-bs-toggle="tooltip" title="{{ strip_tags($question->parent->question) }}">
                        <i class="fas fa-question-circle"></i>
                    </span>
                @endif
                {{ strip_tags($question->question) }}
            </span>

            @foreach ($question->questionGroups as $group)
                <a href="{{ route('admin.exam.questions.index', ['question_group_id' => $group->id]) }}"
                    class="badge bg-info">
                    {{ $group->name }}
                </a>
            @endforeach
            @if ($question->papers->count())
                <div class="small fw-light text-info mt-1">
                    <b>Existing Papers: </b>
                    @foreach ($question->papers->pluck('title') as $title)
                        <em>{{ $title }}</em>
                        <span class="px-1 text-dark">|</span>
                    @endforeach
                </div>
            @endif
        </label>
    </div>
@endforeach
@if ($questions->hasMorePages())
    <div class="text-center" hx-get="{{ $questions->nextPageUrl() }}" hx-trigger="click" hx-swap="afterend"
        hx-on="htmx:afterSettle: this.remove()">
        <a href="javascript:void(0)" class="btn btn-sm btn-primary px-4 mt-3">
            Load More
        </a>
    </div>
@endif
