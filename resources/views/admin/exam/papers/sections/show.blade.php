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
                    hx-include="#question_group_id" hx-trigger="change" hx-target="#questions">
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.exam.papers.sections.update', [$paper, $section]) }}"
        class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body" hx-get="{{ route('admin.exam.htmx.questions.list', ['section_id' => $section->id]) }}"
            hx-trigger="load" id="questions">
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-dark px-3">
                <i class="fas fa-save"></i> Submit
            </button>
        </div>
    </form>
</x-admin.layout>
