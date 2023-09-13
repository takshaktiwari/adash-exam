<x-exam-exam:exam-layout>
    <div class="container py-5">
        @foreach ($papers as $paper)
            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap">
                    <div class="my-2 flex-fill">
                        <h6>{{ $paper->title }}</h6>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span>
                                <b>Total Marks:</b>
                                <span>{{ $paper->questions_sum_marks }}</span>
                            </span>
                            @if ($paper->sections_count)
                                <span class="text-warning px-2">|</span>
                                <span>
                                    <b>Sections:</b>
                                    <span>{{ $paper->sections_count }}</span>
                                </span>
                            @endif
                            <span class="text-warning px-2">|</span>
                            <span>
                                <b>Questions:</b>
                                <span>{{ $paper->questions_count }}</span>
                            </span>
                            <span class="text-warning px-2">|</span>
                            <span>
                                <b>Time:</b>
                                <span>{{ $paper->total_time }} Mins</span>
                            </span>
                            <span class="text-warning px-2">|</span>
                            <a href="{{ route('exam.user-papers.index', [$paper]) }}">
                                <b>Attempts:</b>
                                <span>{{ $paper->user_papers_count }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="my-auto">
                        <a href="{{ route('exam.instructions', [$paper]) }}" class="btn btn-info px-3">
                            Start Exam <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-exam-exam:exam-layout>
