<x-admin.layout>
    <x-admin.breadcrumb title='Create Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'Filter',
            'icon' => 'fas fa-sliders-h',
            'url' => route('admin.exam.user-papers.index', ['filter' => 1]),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <th>#</th>
                    <th>Paper</th>
                    <th>User</th>
                    <th>Questions</th>
                    <th>Stared At</th>
                    <th>Marks</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    @foreach ($userPapers as $key => $userPaper)
                        <tr>
                            <td>{{ $userPapers->firstItem() + $key }}</td>
                            <td class="small">
                                <a href="{{ route('admin.exam.papers.show', [$userPaper->paper]) }}">
                                    {{ $userPaper->paper->title }}
                                </a>
                            </td>
                            <td >
                                <a href="{{ route('admin.users.show', [$userPaper->user]) }}">
                                    {{ $userPaper->user->name }}
                                </a>
                            </td>
                            <td class="text-nowrap">
                                Total: {{ $userPaper->paper->questions_count }}
                                <span class="px-2 text-dark">|</span>
                                Answered: {{ $userPaper->questions->where('status', 'answered')->count() }}
                            </td>
                            <td class="text-nowrap">{{ $userPaper->start_at?->format('d-M-Y h:i A') }}</td>
                            <td>{{ $userPaper->questions->where('status', 'answered')->sum('marks') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.exam.user-papers.show', [$userPaper]) }}" class="btn btn-sm btn-info load-circle">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.exam.user-papers.delete', [$userPaper]) }}" class="btn btn-sm btn-danger load-circle">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $userPapers->links() }}
        </div>
    </div>
</x-admin.layout>
