<x-admin.layout>
    <x-admin.breadcrumb title='Edit Question Groups' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Question Groups', 'url' => route('admin.exam.question-groups.index')], ['text' => 'Edit']]" :actions="[
        [
            'text' => 'All Group',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.question-groups.index'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('admin.exam.question-groups.update', [$questionGroup]) }}" class="card shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <label for="">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $questionGroup->name }}" required>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-dark px-3">
                        <i class="fas fa-save"></i> Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin.layout>
