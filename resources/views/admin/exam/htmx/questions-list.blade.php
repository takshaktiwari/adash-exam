@foreach ($questions as $question)
    <div class="form-check mb-0 ps-0 list-group-item">
        <label class="form-check-label mb-0" style="padding-left: 2rem;">
            <input
                hx-get="{{ route('admin.exam.htmx.questions.attach.toggle', ['question_id' => $question->id, $url_param_name => $url_param_value]) }}"
                hx-trigger="change" hx-swap="none"
                hx-on="htmx:afterSettle: document.getElementById('questions_count').innerHTML = event.detail.xhr.response"
                class="form-check-input" type="checkbox" name="questions[]" value="{{ $question->id }}"
                {{ $model->questions?->pluck('id')->contains($question->id) ? 'checked' : '' }}>
            <p class="mb-0 lh-base">
                @if ($question->children_count)
                    <span class="load-circle fs-12 badge bg-dark">
                        {{ $question->children_count }}
                    </span>
                @endif

                {{ $question->question_id ? '--' : '' }}
                {{ strip_tags($question->question) }}
            </p>
        </label>
        <div class="ms-4">
            <div class="d-flex gap-2 mt-1 flex-wrap">
                <div class="my-auto">
                    <a href="{{ route('admin.exam.questions.edit', [
                        $question,
                        'refer' => [
                            'refer_from' => route('admin.exam.questions.edit', [$question]),
                            'refer_to' => route('admin.exam.papers.questions.edit', [$model]),
                            'method' => 'GET'
                        ]
                    ]) }}"
                        class="text-success fw-bold">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                @foreach ($question->questionGroups as $group)
                    <a href="{{ route('admin.exam.questions.index', ['question_group_id' => $group->id]) }}"
                        class="badge bg-info">
                        {{ $group->name }}
                    </a>
                @endforeach
                @if ($question->papers->count())
                    <div class="small fw-light text-info my-auto">
                        <b>Added in: </b>
                        @foreach ($question->papers->pluck('title') as $title)
                            <em>{{ $title }}</em>
                            @if (!$loop->last)
                                <span class="px-1 text-dark">|</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
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
