<x-admin.layout>
    <x-admin.breadcrumb title='Questions' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Questions']]" :actions="[
        [
            'text' => 'Filter',
            'icon' => 'fas fa-sliders-h',
            'url' => route('admin.exam.questions.index', ['filter' => 1]),
            'class' => 'btn-light btn-loader',
        ],
        [
            'text' => 'Export',
            'icon' => 'fas fa-file-excel',
            'url' => route('admin.exam.questions.export', request()->all()),
            'class' => 'btn-success btn-loader',
        ],
        [
            'text' => 'Add Question',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.questions.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    @if (request('filter'))
        <form action="{{ route('admin.exam.questions.index') }}" class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="">Question Group</label>
                            <select name="question_group_id" id="question_group_id" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach ($questionGroups as $questionGroup)
                                    <option value="{{ $questionGroup->id }}"
                                        {{ request('question_group_id') == $questionGroup->id ? 'selected' : '' }}>
                                        {{ $questionGroup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group mb-0">
                            <label for="">Search Question</label>
                            <input type="text" name="question" class="form-control"
                                value="{{ request('question') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <input type="hidden" name="filter" value="1">
                <button style="submit" class="btn btn-dark px-3">
                    <i class="fas fa-save"></i> Submit
                </button>
                <a href="{{ route('admin.exam.questions.index') }}" class="btn btn-danger px-3">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    @endif

    <div class="card shadow-sm collapse" id="filter-box">
        <div class="card-body">
            <form action="{{ route('admin.exam.questions.index') }}" class="row">
                <div class="col-md-4">
                    <input type="text" name="question" class="form-control mb-sm-0 mb-2" placeholder="Enter Question"
                        value="{{ Request::get('question') }}">
                </div>
                <div class="col-md-2">
                    <input type="submit" class="btn btn-dark px-4">
                </div>
            </form>
        </div>
    </div>


    <div class="card shadow-sm">
        <x-admin.paginator-info :items="$questions" class="card-header" />
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input check_all_question_ids" type="checkbox">
                                    #
                                </label>
                            </div>
                        </th>
                        <th>Question</th>
                        <th>Groups</th>
                        <th>Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $key => $question)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input question_ids" type="checkbox"
                                            value="{{ $question->id }}">
                                        {{ $key + 1 }}
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    @if (!$question->question_id)
                                        <div>
                                            <a href="{{ route('admin.exam.questions.bind', [$question]) }}"
                                                class="no-loader badge {{ $question->children_count ? 'bg-primary' : 'bg-dark' }}">
                                                @if ($question->children_count)
                                                    <span class="fs-14">
                                                        {{ $question->children_count }}
                                                    </span>
                                                @else
                                                    <i class="fas fa-th-list"></i>
                                                @endif
                                            </a>
                                        </div>
                                    @endif

                                    @if ($question->question_id)
                                        <span>
                                            <b>{{ $question->question_id ? '--' : '' }}</b>
                                            {{ strip_tags($question->question) }}
                                        </span>
                                    @else
                                        <a href="{{ route('admin.exam.questions.index', ['question_id' => $question->id]) }}"
                                            class="lc-2 no-loader">
                                            {{ strip_tags($question->question) }}
                                        </a>
                                    @endif
                                </div>
                                @if ($question->papers->count())
                                    <div class="small">
                                        <b>Papers:</b>
                                        @foreach ($question->papers as $paper)
                                            <a href="{{ route('admin.exam.papers.show', [$paper]) }}" class="mr-2">
                                                ({{ $paper->title }})
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                @foreach ($question->questionGroups as $group)
                                    <a href="{{ route('admin.exam.questions.index', ['question_group_id' => $group->id]) }}"
                                        class="badge bg-info fs-11">
                                        {{ $group->name }}
                                    </a>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-dark fs-14">{{ $question->marks }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.exam.questions.show', [$question]) }}"
                                    class="btn btn-sm btn-info load-circle">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.exam.questions.edit', [$question]) }}"
                                    class="btn btn-sm btn-success load-circle" title="Edit Date Slot">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exam.questions.destroy', [$question]) }}"
                                    class="d-inline-block" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button href="" class="btn btn-sm btn-danger" title="Delete Date Slot"
                                        onclick="return confirm('Are you sure to delete this date')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between">
            {{ $questions->links() }}
            <div class="actions">
                <button class="btn btn-danger px-3 delete_questions_btn">
                    <i class="fas fa-trash"></i> Delete Checked
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $(".check_all_question_ids").click(function() {
                    $('.question_ids').not(this).prop('checked', this.checked);
                });

                $(".delete_questions_btn").click(function() {
                    var questionIds = $(".question_ids:checked").map(function() {
                        return $(this).val();
                    }).get();

                    if (!questionIds.length) {
                        alert('Please select questions.');
                        return false;
                    }

                    window.location.href = "{{ route('admin.exam.questions.bulk-delete') }}?question_ids=" +
                        encodeURIComponent(questionIds);
                });
            });
        </script>
    @endpush
</x-admin.layout>
