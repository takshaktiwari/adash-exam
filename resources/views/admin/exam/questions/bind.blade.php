<x-admin.layout>
    <x-admin.breadcrumb title='Question Bind' :links="[['text' => 'Questions', 'url' => route('admin.exam.papers.index')], ['text' => 'Question', 'url' => route('admin.exam.questions.show', [$question])], ['text' => 'Bind']]" :actions="[
        [
            'text' => 'Add Question',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.questions.create'),
            'class' => 'btn-success btn-loader',
        ],
        [
            'text' => 'All Question',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.questions.index'),
            'class' => 'btn-dark btn-loader',
        ],
    ]" />

    @push('styles')
        <script src="https://unpkg.com/htmx.org@1.9.5"
            integrity="sha384-xcuj3WpfgjlKF+FXhSQFQ0ZNr39ln+hwjN3npfM9VBnUskLolQAcN80McRIVOPuO" crossorigin="anonymous">
        </script>
    @endpush

    <div class="card shadow-sm">
        <div class="card-header border-bottom border-dark">
            <h5 class="">Bind Questions</h5>
            <p class="mb-0">
                <b>Question: </b>
                <a href="{{ route('admin.exam.questions.show', [$question]) }}">{{ $question->question }}</a>
            </p>
        </div>
        <div class="card-header d-flex">
            <div style="width: 350px">
                <label for="">Question Group</label>
                <select name="question_group_id" id="question_group_id" class="form-control"
                    hx-get="{{ route('admin.exam.htmx.questions.bind.list', ['question_id' => $question->id]) }}"
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
                    hx-get="{{ route('admin.exam.htmx.questions.bind.list', ['question_id' => $question->id]) }}"
                    hx-include="#question_group_id" hx-trigger="change" hx-target="#questions">
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        @csrf
        <div class="card-body" hx-get="{{ route('admin.exam.htmx.questions.bind.list', ['question_id' => $question->id]) }}"
            hx-trigger="load" id="questions">
        </div>
    </div>

</x-admin.layout>
