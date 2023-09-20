<x-admin.layout>
    <x-admin.breadcrumb title='Questions' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Questions']]" :actions="[
        [
            'text' => 'Filter',
            'icon' => 'fas fa-sliders-h',
            'url' => route('admin.exam.questions.index', ['filter' => 1]),
            'class' => 'btn-light btn-loader',
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
                        <th>#</th>
                        <th>Question</th>
                        <th>Groups</th>
                        <th>Marks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questions as $key => $question)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $question->question }}
                            </td>
                            <td>
                                @foreach ($question->questionGroups as $group)
                                    <a href="{{ route('admin.exam.questions.index', ['question_group_id' => $group->id]) }}"
                                        class="badge bg-info fs-12">
                                        {{ $group->name }}
                                    </a>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-dark fs-14">{{ $question->marks }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.exam.questions.show', [$question]) }}"
                                    class="btn btn-sm btn-info">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.exam.questions.edit', [$question]) }}"
                                    class="btn btn-sm btn-success" title="Edit Date Slot">
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
        <div class="card-footer">
            {{ $questions->links() }}
        </div>
    </div>

</x-admin.layout>
