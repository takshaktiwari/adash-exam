<div {{ $attributes->merge(['class' => '']) }} onselectstart="return false" onpaste="return false;" onCopy="return false"
    onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1">Question Paper</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
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
                        <tr>
                            <th>Marks Obtained</th>
                            <td>{{ $userPaper->questions_sum_marks }}</td>
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
                    <table class="table table-sm mb-0">
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
                            <td>
                                {{ $paper->questions_count - $userPaper->questions->where('status', 'answered')->count() }}
                                Questions
                            </td>
                        </tr>
                        <tr>
                            <th>Correct</th>
                            <td>{{ $userPaper->questions->filter(fn($i) => $i->isCorrect)->count() }}</td>
                        </tr>
                        <tr>
                            <th>Incorrect</th>
                            <td>{{ $userPaper->questions->filter(fn($i) => !$i->isCorrect)->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-3">Questions List</h4>
    <hr />
    @if ($paper->sections_count)
        @foreach ($paper->sections as $section)
            <h5 class="fw-bold"><i class="fas fa-arrow-right"></i> {{ $section->name }}</h5>
            @foreach ($section->questions as $question)
                <x-exam-exam:user-question-card :question="$question" />
            @endforeach
            <hr />
        @endforeach
    @else
        @foreach ($paper->questions as $question)
            <x-exam-exam:user-question-card :question="$question" />
        @endforeach
    @endif
</div>
