<x-admin.layout>
    <x-admin.breadcrumb title='Papers Questions' :links="[
        ['text' => 'Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Show', 'url' => route('admin.exam.papers.show', [$paper])],
        ['text' => 'Sections', 'url' => route('admin.exam.papers.sections.index', [$paper])],
        ['text' => 'Edit'],
    ]" :actions="[
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
        <div class="card-body">
            <p><b>Paper:</b> {{ $paper->title }}</p>
            <p class="mb-10"><b>Section:</b> {{ $section->name }}</p>
            <p class="mb-10">
                <b>Questions:</b>
                <span id="questions_count">
                    {{ $section->questions_count }}
                </span>
            </p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex">
            <div style="width: 350px">
                <label for="">Question Group</label>
                <select name="question_group_id" id="question_group_id" class="form-control"
                    hx-get="{{ route('admin.exam.htmx.questions.list', ['section_id' => $section->id]) }}"
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
                    hx-get="{{ route('admin.exam.htmx.questions.list', ['section_id' => $section->id]) }}"
                    hx-include="#question_group_id" hx-trigger="change, load" hx-target="#questions">
            </div>
        </div>
        <div class="text-end">
            <a href="{{ url()->current() }}" class="text-danger"><i class="fas fa-times"></i> Reset</a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.exam.papers.sections.update', [$paper, $section]) }}"
        class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body" id="questions">
        </div>
    </form>

</x-admin.layout>
