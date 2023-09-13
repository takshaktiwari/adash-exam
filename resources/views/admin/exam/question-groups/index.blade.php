<x-admin.layout>
    <x-admin.breadcrumb title='Question Groups' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Question Groups']]" :actions="[
        [
            'text' => 'Add Group',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.question-groups.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="card shadow-sm">
        <x-admin.paginator-info :items="$questionGroups" class="card-header" />
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Questions</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($questionGroups as $group)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $group->name }}</td>
                            <td>{{ $group->questions_count }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.exam.question-groups.edit', [$group]) }}" class="btn btn-sm btn-success load-circle"
                                    title="Edit Paper">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exam.question-groups.destroy', [$group]) }}"
                                    class="d-inline-block" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button href="" class="btn btn-sm btn-danger delete-alert" title="Delete Date Slot">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-admin.layout>
