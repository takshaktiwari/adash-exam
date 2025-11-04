@php
    $isCorrect = false;
    $badgeClass = 'bg-secondary';
    if (!$question->userQuestion || $question->userQuestion?->status == 'marked') {
        $badgeClass = 'bg-secondary';
    } elseif ($question->userQuestion->correct_option_id == $question->userQuestion->user_option_id) {
        $isCorrect = true;
        $badgeClass = 'bg-success';
    } else {
        $badgeClass = 'bg-danger';
    }
@endphp
<div class="card shadow-sm mb-2 ">
    <div class="card-header d-flex justify-content-between ">
        <div>
            <span class="btn btn-sm px-4 py-1 rounded-pill {{ $badgeClass }} text-white">
                @if (!$question->userQuestion || $question->userQuestion?->status == 'marked')
                    Not Answered
                @elseif($question->userQuestion->correct_option_id == $question->userQuestion->user_option_id)
                    Correct
                @else
                    Incorrect
                @endif
            </span>
        </div>
        <div class="my-auto">
            Marks: {{ $question->userQuestion ? $question->userQuestion->marks : 0 }}
        </div>
    </div>
    <div class="card-body border-bottom py-3" style="font-size: 110%; font-weight: 500;">
        {!! nl2br($question->question) !!}
    </div>
    <div class="card-body border-bottom py-2">
        @foreach ($question->options as $option)
            <div class="d-flex gap-3 mb-2">
                <div class="icon">
                    @if ($option->correct_ans)
                        <i class="fas fa-check-square"></i>
                    @else
                        <i class="far fa-square"></i>
                    @endif
                </div>
                <div @class([
                    'text-dark' => $option->correct_ans,
                    'text-gray' => !$option->correct_ans,
                ])>
                    {{ $option->option_text }}
                </div>
            </div>
        @endforeach

        @if ($question->userQuestion && $question->userQuestion->status == 'answered')
            <div @class(['mt-3', 'text-success' => $isCorrect, 'text-danger' => !$isCorrect]) style="font-size: 110%; font-weight: 500;">
                <b>User Answer: </b>
                {{ $question->userQuestion->user_answer_text }}
            </div>
            @else
            <div class="mt-2"></div>
        @endif

        <div class="mt-1 text-dark" style="font-size: 110%; font-weight: 500;">
            <b>Correct Answer:</b>
            {{ $question->options->filter(fn ($option) => $option->correct_ans)->first()?->option_text ?? 'N/A' }}
        </div>
    </div>
    @if ($question->answer)
        <div class="card-body py-3">
            <b>Explanation: </b>
            {{ $question->answer }}
        </div>
    @endif
</div>
@push('styles')
    <style>
        .text-gray {
            color: gray;
        }
    </style>
@endpush
