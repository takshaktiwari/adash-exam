<x-admin.layout>
    <x-admin.breadcrumb title='Question Show' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Show'],
    ]" :actions="[
        [
            'text' => 'All Questions',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.questions.index'),
            'class' => 'btn-success btn-loader',
        ],
        [
            'text' => 'Create Questions',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.questions.create'),
            'class' => 'btn-dark btn-loader',
        ],
    ]" />

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-4">{!! $question->question !!}</div>
            <img src="{{ storage($question->image) }}" alt="question img" class="rounded-2" style="max-height: 200px">
            <hr />
            <p class="mb-1"><b>Options:</b></p>
            <ul>
                @foreach ($question->options as $option)
                    <li class="{{ $option->correct_ans ? 'text-success fw-bold' : '' }}">
                        {{ $option->option_text }}
                        @if($option->correct_ans)
                            <i class="fas fa-check"></i>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-admin.layout>