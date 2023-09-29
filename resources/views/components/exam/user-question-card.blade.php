@php

    $badgeClass = 'bg-secondary';
    if (!$question->userQuestion || $question->userQuestion?->status == 'marked') {
        $badgeClass = 'bg-secondary';
    } elseif ($question->userQuestion->correct_option_id == $question->userQuestion->user_option_id) {
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
        <div>
            Marks: {{ $question->userQuestion ? $question->userQuestion->marks : 0 }}
        </div>
    </div>
    <div class="card-body border-bottom">
        {!! nl2br($question->question) !!}
    </div>
    <div class="card-body">
        @if ($question->userQuestion && $question->userQuestion->status == 'answered')
            <div>
                <b>User Answer: </b>
                {{ $question->userQuestion->user_answer_text }}
            </div>
        @endif
        <div class="mb-2">
            <b>Correct Answer: </b>
            {{ $question->userQuestion?->correct_answer_text
                ? $question->userQuestion->correct_answer_text
                : $question->correctOption->option_text }}
        </div>
        @if ($question->answer)
            <div>
                <b>Explanation: </b>
                {{ $question->answer }}
            </div>
        @endif
    </div>
</div>
