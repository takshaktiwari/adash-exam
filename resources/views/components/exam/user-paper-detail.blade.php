<div {{ $attributes->merge(['class' => '']) }}>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1">Question Paper</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th colspan="2">{{ $paper->title }}</th>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td>{{ $paper->total_time }} Mins</td>
                        </tr>
                        <tr>
                            <th>Questions</th>
                            <td>{{ $paper->questions_count }} Questions</td>
                        </tr>
                        <tr>
                            <th>Total Marks</th>
                            <td>{{ $paper->questions_sum_marks }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1">Exam Report</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Answered</th>
                            <td>{{ $userPaper->questions->where('status', 'answered')->count() }} Questions</td>
                        </tr>
                        <tr>
                            <th>Marked</th>
                            <td>{{ $userPaper->questions->where('status', 'marked')->count() }} Questions</td>
                        </tr>
                        <tr>
                            <th>Not Answered</th>
                            <td>{{ $paper->questions_count - $userPaper->questions->where('status', 'answered')->count() }}
                                Questions</td>
                        </tr>
                        <tr>
                            <th>Marks Obtained</th>
                            <td>{{ $userPaper->questions_sum_marks }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-3">Questions List</h4>

    @foreach ($paper->questions as $question)
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
                {{ strip_tags($question->question) }}
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
    @endforeach
</div>
