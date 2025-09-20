<x-admin.layout>
    <x-admin.breadcrumb title='Papers Questions' :links="[['text' => 'Papers', 'url' => route('admin.exam.papers.index')], ['text' => 'Questions']]" :actions="[
        [
            'text' => 'Add Paper',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    @push('styles')
        <script src="https://unpkg.com/htmx.org@1.9.5"
            integrity="sha384-xcuj3WpfgjlKF+FXhSQFQ0ZNr39ln+hwjN3npfM9VBnUskLolQAcN80McRIVOPuO" crossorigin="anonymous">
        </script>
    @endpush


    <div class="card shadow-sm">
        <div class="card-header border-bottom border-dark">
            <h5 class="">
                <a href="{{ route('admin.exam.papers.show', [$paper]) }}">
                    {{ $paper->title }}
                </a>
            </h5>
            <p class="mb-0">
                <b>Questions: </b>
                <span id="questions_count">{{ $paper->questions_count }}</span>
            </p>
        </div>
        <form method="POST" action="{{ route('admin.exam.papers.questions.auto-add', [$paper]) }}" class="card-header">
            <div class="d-flex gap-2">
                <div style="width: 350px">
                    <label for="">Question Group</label>
                    <select name="question_group_id" id="question_group_id" class="form-control"
                        hx-get="{{ route('admin.exam.htmx.questions.list', ['paper_id' => $paper->id]) }}"
                        hx-include="#question_search" hx-trigger="change" hx-target="#questions">
                        <option value="">-- Question group --</option>
                        @foreach ($questionGroups as $group)
                            <option value="{{ $group->id }}">
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-fill">
                    <label for="">Search</label>
                    <input type="text" name="search" id="question_search" class="form-control"
                        placeholder="Search question"
                        hx-get="{{ route('admin.exam.htmx.questions.list', ['paper_id' => $paper->id]) }}"
                        hx-include="#question_group_id" hx-trigger="change, load" hx-target="#questions">
                </div>
            </div>
            <div class="d-flex gap-2 mt-2">
                <div class="form-check pt-2">
                    <label class="form-check-label mb-0">
                        <input class="form-check-input" type="checkbox" id="not_used" name="not_used" value="1"
                            hx-get="{{ route('admin.exam.htmx.questions.list', ['paper_id' => $paper->id]) }}"
                            hx-include="#question_group_id, #question_search" hx-trigger="change"
                            hx-target="#questions">
                        <span class="text-nowrap">Not Used In Papers</span>
                    </label>
                </div>
            </div>
            <hr />
            <div class="d-flex gap-3 mt-2">
                <div class="my-auto">
                    <div class="input-group">
                        <span class="input-group-text">Auto Add Ques.</span>
                        <input type="number" name="auto_add_questions" id="auto_add" class="form-control" placeholder="eg. 25" style="max-width: 80px;" required>
                    </div>
                </div>
                <div class="my-auto">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure to auto add questions?')">
                        <i class="fas fa-save"></i> Add Questions
                    </button>
                </div>
            </div>
            <div class="text-end">
                <a href="{{ url()->current() }}" class="text-danger"><i class="fas fa-times"></i> Reset</a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.exam.papers.questions.update', [$paper]) }}" class="card shadow-sm">
        @csrf
        <div class="card-body" id="questions">
        </div>
    </form>

</x-admin.layout>
