<x-admin.layout>
    <x-admin.breadcrumb title='Create Question Groups' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Question Groups', 'url' => route('admin.exam.question-groups.index')], ['text' => 'Create']]" :actions="[
        [
            'text' => 'All Groups',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.question-groups.index'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="{{ route('admin.exam.question-groups.store') }}" class="card shadow-sm">
                @csrf
                <div class="card-body">
                    <label for="">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
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
