<x-admin.layout>
    <x-admin.breadcrumb title='Papers Questions' :links="[
        ['text' => 'Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Questions'],
    ]" :actions="[
        [
            'text' => 'Add Paper',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    @push('styles')
        <script src="https://unpkg.com/htmx.org@1.9.5" integrity="sha384-xcuj3WpfgjlKF+FXhSQFQ0ZNr39ln+hwjN3npfM9VBnUskLolQAcN80McRIVOPuO" crossorigin="anonymous"></script>
    @endpush


    <form method="POST" action="{{ route('admin.exam.papers.questions.update', [$paper]) }}" class="card shadow-sm">
        @csrf
        <div class="card-header">
            <h5 class="my-auto">{{ $paper->title }}</h5>
        </div>
        <div class="card-header">
            <input type="text" name="search" class="form-control" placeholder="Search question" hx-get="{{ route('admin.exam.htmx.questions.list', ['paper_id' => $paper->id]) }}" hx-trigger="change" hx-target="#questions">
        </div>
        <div class="card-body" hx-get="{{ route('admin.exam.htmx.questions.list', ['paper_id' => $paper->id]) }}" hx-trigger="load" id="questions">
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-dark px-3">
                <i class="fas fa-save"></i> Submit
            </button>
        </div>
    </form>
</x-admin.layout>
