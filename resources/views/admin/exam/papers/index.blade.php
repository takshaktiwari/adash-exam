<x-admin.layout>
    <x-admin.breadcrumb title='Question Papers' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Question Papers']]" :actions="[
        [
            'text' => 'Add Paper',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="card shadow-sm">
        <x-admin.paginator-info :items="$papers" class="card-header" />
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Paper Title</th>
                        <th>Sections</th>
                        <th>Ques</th>
                        <th>Marks</th>
                        <th>Time</th>
                        <th>Activate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($papers as $paper)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="small">
                                <span class="d-block">
                                    {{ $paper->title }}
                                </span>
                                <span class="badge bg-{{ $paper->status ? 'success' : 'danger' }} fs-12">
                                    {{ $paper->status() }}
                                </span>
                            </td>
                            <td>{{ $paper?->sections_count }}</td>
                            <td>{{ $paper?->questions_count }}</td>
                            <td>{{ $paper->questions_sum_marks }}</td>
                            <td>{{ $paper->total_time }} <span class="small">Min.</span></td>
                            <td>
                                <span title="Activation date time" class="text-nowrap">
                                    {{ date('d-M-Y h:i A', strtotime($paper->activate_at)) }}
                                </span>
                                <div title="Expiration Date time" class="text-danger small">
                                    {{ date('d-M-Y h:i A', strtotime($paper->expire_at)) }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('exam.instructions', [$paper]) }}"
                                    class="btn btn-sm btn-info load-circle" title="Start Paper" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="{{ route('admin.exam.papers.show', [$paper]) }}"
                                    class="btn btn-sm btn-primary load-circle" title="Paper Questions">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.exam.papers.edit', [$paper]) }}" class="btn btn-sm btn-success"
                                    title="Edit Paper">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.exam.papers.destroy', [$paper]) }}"
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
        <div class="card-footer">
            {{ $papers->links() }}
        </div>
    </div>


</x-admin.layout>
