<div {{ $attributes->merge(['class' => '']) }}>
    <table class="table">
        <thead>
            <th>#</th>
            <th>Paper</th>
            <th>Questions</th>
            <th>Stared At</th>
            <th>Marks</th>
            <th>Action</th>
        </thead>
        <tbody>
            @foreach ($userPapers as $key => $userPaper)
                <tr>
                    <td>{{ $userPapers->firstItem() + $key }}</td>
                    <td>{{ $paper->title }}</td>
                    <td>
                        Total: {{ $paper->questions_count }}
                        <span class="px-2 text-secondary">|</span>
                        Answered: {{ $userPaper->questions->where('status', 'answered')->count() }}
                        <span class="px-2 text-secondary">|</span>
                        Marked: {{ $userPaper->questions->where('status', 'marked')->count() }}
                    </td>
                    <td>{{ $userPaper->start_at?->format('d-M-Y h:i A') }}</td>
                    <td>{{ $userPaper->questions->where('status', 'answered')->sum('marks') }}</td>
                    <td>
                        <a href="{{ route('exam.user-papers.show', [$paper, $userPaper]) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-info-circle"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $userPapers->links() }}
    </div>
</div>
