<x-admin.layout>
    <x-admin.breadcrumb title='Create Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'All Questions',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.questions.index'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    @push('styles')
        <style>
            /* HIDE RADIO */
            [type=radio] {
                position: absolute;
                opacity: 0 !important;
                visibility: hidden;
                width: 0;
                height: 0;
            }

            /* IMAGE STYLES */
            [type=radio]+.answercheck {
                cursor: pointer;
                width: 40px;
                background-color: #ececec;
            }

            [type=radio]+.answercheck:after {
                content: '\f00d';
                font-family: 'Font Awesome 5 Free';
                font-weight: 800;
                font-size: 16px;
                color: #b9b9b9;
            }

            /* CHECKED STYLES */
            [type=radio]:checked+.answercheck:after {
                content: '\f00c';
                font-family: 'Font Awesome 5 Free';
                color: #333;
            }
        </style>
    @endpush


    <form method="POST" action="{{ route('admin.exam.questions.store') }}" class="card shadow-sm"
        enctype="multipart/form-data" id="question_form">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="">Parent Question</label>
                <select name="question_id" id="question_id" class="form-control select2">
                    <option value="">-- Select --</option>
                    @foreach ($questions as $question)
                        <option value="{{ $question->id }}" @selected($parentQuestion?->id == $question->id)>
                            {{ strip_tags($question->question) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if (!$parentQuestion)
                <div class="text-end">
                    <a href="javascript:void(0)" id="add_context" class="text-primary fs-6" data-bs-toggle="collapse" data-bs-target="#context_block">
                        <i class="fas fa-plus"></i> Add Context / Passage
                    </a>
                </div>
                <div id="context_block" class="form-group collapse">
                    <label for="">Context / Passage </label>
                    <textarea name="context" rows="2" class="form-control text-editor" placeholder="Write your context">{{ old('context') }}</textarea>
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Question <span class="text-danger">*</span></label>
                        <textarea name="question" rows="2" class="form-control text-editor" placeholder="Write your question">{{ old('question') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Descriptive Answer </label>
                        <textarea name="answer" rows="4" class="form-control text-editor" placeholder="Describe your answer"
                            maxlength="499">{{ old('answer') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="">Question Groups <span class="text-danger">*</span></label>
                <select name="question_group_id[]" id="question_group_id" class="form-control select2" multiple
                    required>
                    <option value="">-- Select --</option>
                    @foreach ($questionGroups as $group)
                        <option value="{{ $group->id }}" @selected($parentQuestion?->questionGroups->pluck('id')->contains($group->id))>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="">Image </label>
                <input type="file" name="image" class="form-control" />
            </div>
            <div class="form-group">
                <label for="">Marks <span class="text-danger">*</span></label>
                <input type="number" name="marks" class="form-control" required placeholder="eg. 2"
                    value="{{ old('marks') }}">
            </div>
            <div class="form-group">
                <label for="">
                    Question Choices <span class="text-danger">*</span>

                    <a href="javascript:void(0)" id="add_option_btn" class="badge bg-primary">+ Add More</a>
                </label>
                <div id="question_options">
                    @for ($i = 1; $i <= 4; $i++)
                        <div class="mb-3 option">
                            <div class="d-flex">
                                <div>
                                    <input type="file" name="ques_option_img[{{ $i }}]"
                                        class="form-control rounded-0" accept="image/*">
                                </div>
                                <div class="flex-fill">
                                    <input type="text" name="ques_option[{{ $i }}]"
                                        class="form-control rounded-0" placeholder="Option Choice {{ $i }}"
                                        value="{{ old('ques_option')[$i] ?? '' }}" maxlength="250">
                                </div>

                                <label class="mb-0">
                                    <input type="radio" name="correct_ans" value="{{ $i }}"
                                        required="">
                                    <div class="answercheck form-control rounded-0"></div>
                                </label>
                            </div>
                            <a href="javascript:void(0)" class="fw-bold text-danger small remove_option mt-n1 d-block">
                                <i class="fas fa-times"></i> Remove
                            </a>
                        </div>
                    @endfor
                </div>
            </div>


        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-dark px-4" id="question_submit_btn">
                <i class="fas fa-save"></i> Submit
            </button>
        </div>
    </form>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.text-editor').summernote({
                    placeholder: 'Write here',
                    tabsize: 4,
                    height: 200
                });

                $("#question_id").on('change', function() {
                    var questionId = $(this).val();
                    if (questionId) {
                        window.location.href = window.location.href + `?question_id=${questionId}`;
                    }
                });

                $("#question_submit_btn").click(function() {
                    var correctAnsVal = $('input[name="correct_ans"]:checked').val();
                    if (!correctAnsVal) {
                        alert('Please check the correct answer');
                        return false;
                    }
                });

                $("#add_option_btn").click(function() {
                    var optionsCount = $("#question_options .option").length;
                    var newOptionCount = ++optionsCount;

                    $("#question_options").append(`
                        <div class="mb-3 option">
                            <div class="d-flex">
                                <div>
                                    <input type="file" name="ques_option_img[${newOptionCount}]"
                                        class="form-control rounded-0" accept="image/*">
                                </div>
                                <div class="flex-fill">
                                    <input type="text" name="ques_option[${newOptionCount}]"
                                        class="form-control rounded-0"
                                        placeholder="Option Choice ${newOptionCount}"
                                        value="" maxlength="250" >
                                </div>

                                <label class="mb-0">
                                    <input type="radio" name="correct_ans" value="${newOptionCount}"
                                        required="">
                                    <div class="answercheck form-control rounded-0"></div>
                                </label>
                            </div>
                            <a href="javascript:void(0)" class="fw-bold text-danger small remove_option mt-n1 d-block">
                                <i class="fas fa-times"></i> Remove
                            </a>
                        </div>
                    `);
                });

                $('#question_options').on('click', '.remove_option', function() {
                    $(this).parent().remove();
                });
            });
        </script>
    @endpush
</x-admin.layout>
