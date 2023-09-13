<x-exam-exam:exam-layout>
    <style>
        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #888;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        #sidebar {
            width: 300px;
            min-width: 300px;
            height: 100vh;
            color: white;
            padding-bottom: 10rem;
            overflow-x: hidden;
            overflow-y: scroll;
        }

        #sidebar .user img {
            max-width: 100px
        }

        #sidebar .stats {
            font-size: 0.85rem;
        }

        #sidebar .stats button {
            width: 36px;
            padding: 2px !important;
            font-size: 0.9rem;
        }

        #sidebar .question_section .question_item {
            max-width: 34px;
            width: 100%;
            padding: 2px;
        }

        #question_area {
            position: relative;
            height: 100vh;
            padding-bottom: 15rem;
            overflow-x: hidden;
            overflow-y: scroll;
        }

        #question_area .question {
            font-size: 1.1rem;
        }

        #question_action {
            position: fixed;
            bottom: 0px;
            width: calc(100% - 300px);
            left: 300px;
        }

        .bg-marked {
            background-color: #f3ff43 !important;
            border-color: #f3ff43 !important;
            color: black !important;
        }
        .bg-mark_review{
            background-color: #b3bf00 !important;
            border-color: #b3bf00 !important;
            color: black !important;
        }

        .bg-answered {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: white !important;
        }

        .bg-not-answered {
            background-color: #ffffff !important;
            border-color: #ffffff !important;
            color: black !important;
        }
    </style>
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark py-1 border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="javascript:void(0)">Logo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mynavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mynavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal"
                            data-bs-target="#instructions_modal">
                            <i class="fa-solid fa-info-circle"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" id="fullscreen_btn">
                            <i class="fa-solid fa-expand"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <form action="{{ route('exam.question-save', [$paper, $question]) }}" method="POST" id="wrapper" class="d-flex">
        @csrf
        <x-exam-exam:exam-sidebar :paper="$paper" :userQuestions="$userQuestions" :question="$question" />

        <div id="question_area" class="flex-fill px-4 pt-4">
            <div class="question">
                <b>{{ $questionKey + 1 }}.</b>
                {{ $question->question }}
            </div>
            <div class="options mt-4">
                <ul class="list-group">
                    @foreach ($question->options as $option)
                        <div class="form-check list-group-item ps-4">
                            <label class="form-check-label ps-3" for="option_{{ $option->id }}">
                                <input type="radio" class="form-check-input" id="option_{{ $option->id }}"
                                    name="user_option" value="{{ $option->id }}" required
                                    {{ $userQuestion?->user_option_id == $option->id ? 'checked' : '' }}>
                                <span>{{ $option->option_text }}</span>
                            </label>
                        </div>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="question_action" class="d-flex justify-content-between bg-light border-top border-dark py-2 px-4">
            <div>
                @if ($questions->get($questionKey - 1))
                    <a href="{{ route('exam.paper', [$paper, 'question_id' => $questions->get($questionKey - 1)]) }}"
                        class="btn btn-info px-3">
                        <i class="fa-solid fa-backward"></i> Prev
                    </a>
                @endif
                @if ($questions->get($questionKey + 1))
                    <a href="{{ route('exam.paper', [$paper, 'question_id' => $questions->get($questionKey + 1)]) }}"
                        class="btn btn-info px-3">
                        Next <i class="fa-solid fa-forward"></i>
                    </a>
                @endif
            </div>
            <div>
                <a href="{{ route('exam.question-mark', [$paper, $question, 'next_question_id' => $questions->get($questionKey + 1)]) }}"
                    class="btn bg-marked px-3">
                    <i class="fa-solid fa-marker"></i> Mark For Later
                </a>
                <button type="submit" class="btn bg-mark_review px-3" onclick="document.getElementById('input_mark_review').value = 1">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save for review
                </button>
            </div>
            <div>
                <input type="hidden" name="mark_review" id="input_mark_review" value="">
                <input type="hidden" name="next_question_id" value="{{ $questions->get($questionKey + 1) }}">
                <button type="submit" class="btn btn-success px-3" onclick="document.getElementById('input_mark_review').value = ''">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ $questions->get($questionKey + 1) ? 'Save & Next' : 'Save Question' }}
                </button>
            </div>
        </div>
    </form>

    <div class="modal" id="instructions_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Instructions</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    {!! $paper->instruction !!}
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal" id="exam_statistics">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title">{{ $paper->title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th>Total Questions:</th>
                                <td>{{ $paper->questions_count }}</td>
                            </tr>
                            @if ($paper->sections_count)
                                <tr>
                                    <th>Sections:</th>
                                    <td>{{ $paper->sections_count }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Answered:</th>
                                <td>{{ $userQuestions->where('status', 'answered')->count() + $userQuestions->where('status', 'mark_review')->count() }}</td>
                            </tr>
                            <tr>
                                <th>For Review:</th>
                                <td>{{ $userQuestions->where('status', 'mark_review')->count() }}</td>
                            </tr>
                            <tr>
                                <th>Marked For Later:</th>
                                <td>{{ $userQuestions->where('status', 'marked')->count() }}</td>
                            </tr>
                            <tr>
                                <th>Not Answered:</th>
                                <td>{{ $paper->questions_count - $userQuestions->where('status', 'answered')->count() }}
                                </td>
                            </tr>
                            <tr>
                                <th>Total marks:</th>
                                <td>{{ $paper->questions_sum_marks }}</td>
                            </tr>
                            <tr>
                                <th>Exam end at:</th>
                                <td>{{ $userPaper->end_at->format('d-M-Y h:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer justify-content-center">
                    <a hx-boost="false" href="{{ route('exam.submit', [$paper]) }}" class="btn btn-info px-3"
                        onclick="closeFullscreen()">
                        <i class="fas fa-save"></i> Submit Exam
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>

            </div>
        </div>
    </div>


</x-exam-exam:exam-layout>
