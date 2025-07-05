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

        #question_area .context *:last-child {
            margin-bottom: 0px;
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

        .bg-mark_review {
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

        #question_area .form-check-input[type=radio] {
            border-color: #8040f7;
        }

        @media only screen and (max-width: 767px) {
            #question_action {
                width: 100%;
                left: 0px;
            }

            #question_action .btn {
                font-size: 0.8rem
            }

            #sidebar {
                display: none;
            }

            #sidebar.show {
                display: block;
            }


        }
    </style>
    <x-exam-exam:exam-navbar />
    <div id="wrapper" class="d-flex" onselectstart="return false" onpaste="return false;" onCopy="return false"
        onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off>
        @csrf
        <x-exam-exam:exam-sidebar :paper="$paper" :userQuestions="$userQuestions" :question="$question" />

        <form action="{{ route('exam.question-save', [$paper, $question]) }}" method="POST" class="flex-fill"
            id="question_form">
            @csrf
            <div id="question_area" class="flex-fill px-4 pt-4">
                <div class="question">
                    <div class="d-flex gap-2 mb-2">
                        <b>{{ $questionKey + 1 }}.</b>
                        <div>
                            {!! nl2br($question->question) !!}
                        </div>
                    </div>
                    @if ($question->context || $question?->parent?->context)
                        <div class="alert alert-info context mb-2 small py-2 px-3">
                            <p class="mb-1 fw-bold">Context:</p>
                            {!! $question->context ?? $question?->parent?->context !!}
                        </div>
                    @endif
                    @if ($question->image)
                        <a hx-boost="false" href="{{ storage($question->image) }}" data-fancybox>
                            <img src="{{ storage($question->image) }}" alt="question img" class="rounded-2 mt-2"
                                style="max-height: 200px">
                        </a>
                    @endif
                </div>
                <div class="options mt-4">
                    <ul class="list-group">
                        @foreach ($question->options as $option)
                            <div class="form-check list-group-item ps-4">
                                <label class="form-check-label ps-3" for="option_{{ $option->id }}">
                                    <input type="radio" class="form-check-input" id="option_{{ $option->id }}"
                                        name="user_option" value="{{ $option->id }}" required
                                        {{ $userQuestion?->user_option_id == $option->id ? 'checked' : '' }}>

                                    <div class="d-flex gap-2">
                                        @if ($option->option_img)
                                            <img src="{{ storage($option->option_img) }}" alt="option_img"
                                                class="rounded mb-2" style="max-height: 150px;">
                                        @endif
                                        @if ($option->option_text)
                                            <p class="mb-0">{{ $option->option_text }}</p>
                                        @endif
                                    </div>

                                </label>
                            </div>
                        @endforeach
                    </ul>

                    <div class="text-end mt-3" id="reset_button"
                        style="display: {{ $userQuestion?->user_option_id ? 'block' : 'none' }}">
                        <a href="{{ route('exam.question-reset', [$paper, $question]) }}"
                            class="btn btn-sm btn-danger px-3"> <i class="fas fa-times"></i> Reset</a>
                    </div>
                </div>
            </div>
            <div id="question_action"
                class="d-flex gap-2 justify-content-between bg-light border-top border-dark py-2 px-4">
                <div class="d-flex flex-wrap gap-2">
                    @if ($questions->get($questionKey - 1))
                        <a href="{{ route('exam.paper', [$paper, 'question_id' => $questions->get($questionKey - 1)]) }}"
                            class="btn btn-info px-md-3 px-2 my-auto text-nowrap action_btn">
                            <i class="fa-solid fa-backward"></i> Prev
                        </a>
                    @endif
                    @if ($questions->get($questionKey + 1))
                        <a href="{{ route('exam.paper', [$paper, 'question_id' => $questions->get($questionKey + 1)]) }}"
                            class="btn btn-info px-md-3 px-2 my-auto action_btn">
                            Next <i class="fa-solid fa-forward"></i>
                        </a>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <a href="{{ route('exam.question-mark', [$paper, $question, 'next_question_id' => $questions->get($questionKey + 1)]) }}"
                        class="btn bg-marked px-md-3 px-2 action_btn">
                        <i class="fa-solid fa-marker"></i> Mark For Later
                    </a>
                    <button type="submit" class="btn bg-mark_review px-md-3 px-2 action_btn"
                        onclick="document.getElementById('input_mark_review').value = 1">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save for review
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <input type="hidden" name="mark_review" id="input_mark_review" value="">
                    <input type="hidden" name="next_question_id" value="{{ $questions->get($questionKey + 1) }}">

                    @if ($questions->get($questionKey + 1))
                        <button type="submit" class="btn btn-success px-md-3 px-2 action_btn"
                            onclick="document.getElementById('input_mark_review').value = ''">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save & Next
                        </button>
                    @else
                        <button type="submit" class="btn btn-success px-md-3 px-2 action_btn"
                            onclick="document.getElementById('input_mark_review').value = ''">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save
                        </button>
                    @endif

                    @if (!$questions->get($questionKey + 1) && $userQuestion?->user_option_id)
                        <a href="" class="btn btn-primary px-md-3 px-2" data-bs-toggle="modal"
                            data-bs-target="#exam_statistics">
                            <i class="fa-solid fa-save"></i> Submit Exam
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="modal" id="instructions_modal">
        <div class="modal-dialog modal-xl">
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
                                <td>{{ $userQuestions->where('status', 'answered')->count() + $userQuestions->where('status', 'mark_review')->count() }}
                                </td>
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
        @if (request('is_end'))
            <script>
                $(document).ready(function() {
                    $("#exam_statistics").modal('show');
                });
            </script>
        @endif
        <script>
            $(document).ready(function() {
                Fancybox.bind("[data-fancybox]", {});

                $("[name=user_option]").change(function() {
                    $('#reset_button').css('display', 'block');
                });
            });


            $("a.action_btn").click(function() {
                $(this).addClass('disabled');
                $(this).html('Please wait');
            });
            $("button.action_btn").click(function() {
                if ($("#question_form")[0].checkValidity()) {
                    $(this).addClass('disabled');
                    $(this).html('Please wait');
                }
            });

            // Set the date we're counting down to
            var countDownDate = new Date("{{ session('exam.end_at') }}").getTime();

            // Update the count down every 1 second
            var x = setInterval(function() {

                // Get today's date and time
                var now = new Date().getTime();

                // Find the distance between now and the count down date
                var distance = countDownDate - now;

                // Time calculations for days, hours, minutes and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Display the result in the element with id="demo"
                var timerText = '';
                if (days) {
                    timerText = timerText + days + ":";
                }
                timerText = hours.toString().padStart(2, '0') + ":" + minutes.toString().padStart(2, '0') + ":" +
                    seconds.toString().padStart(2, '0');

                document.getElementById("time_left_timer").innerHTML = timerText;

                if (distance / 1000 < 300 && distance / 1000 > 299) {
                    alert('Time is ticking. You have only 5 mins left to complete the exam.');
                }

                if (distance / 1000 < 120 && distance / 1000 > 119) {
                    alert('Time is ticking. You have only 2 mins left to complete the exam.');
                }

                // If the count down is finished, write some text
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("time_left_timer").innerHTML = "EXPIRED";
                    window.location.href = "{{ route('exam.submit', [$paper]) }}";
                }
            }, 1000);
        </script>
    @endpush
</x-exam-exam:exam-layout>
