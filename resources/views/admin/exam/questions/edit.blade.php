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
        [
            'text' => 'Create Questions',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.questions.create'),
            'class' => 'btn-dark btn-loader',
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


    <form method="POST" action="{{ route('admin.exam.questions.update', [$question]) }}" class="card shadow-sm"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="">Parent Question</label>
                <select name="question_id" id="question_id" class="form-control select2">
                    <option value="">-- Select --</option>
                    @foreach ($questions as $ques)
                        <option value="{{ $ques->id }}" @selected($question->question_id == $ques->id)>
                            {{ strip_tags($ques->question) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Question <span class="text-danger">*</span></label>
                        <textarea name="question" rows="2" class="form-control text-editor" placeholder="Write your question">{{ $question->question }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Descriptive Answer </label>
                        <textarea name="answer" rows="4" class="form-control text-editor" placeholder="Describe your answer">{{ $question->answer }}</textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="">Question Groups <span class="text-danger">*</span></label>
                <select name="question_group_id[]" id="question_group_id" class="form-control select2" multiple
                    required>
                    <option value="">-- Select --</option>
                    @foreach ($questionGroups as $group)
                        <option value="{{ $group->id }}" @selected($question->questionGroups->pluck('id')->contains($group->id))>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-3">
                @if ($question->image)
                    <div class="">
                        <img src="{{ storage($question->image) }}" alt="question img" class="rounded-2"
                            style="max-height: 65px">
                    </div>
                @endif
                <div class="form-group flex-fill">
                    <label for="">Image </label>
                    <input type="file" name="image" class="form-control" />
                </div>
            </div>
            <div class="form-group">
                <label for="">Marks <span class="text-danger">*</span></label>
                <input type="number" name="marks" class="form-control" required placeholder="eg. 2"
                    value="{{ $question->marks }}">
            </div>
            <div class="form-group">
                <label for="">
                    Question Choices <span class="text-danger">*</span>
                    <a href="javascript:void(0)" id="add_option_btn" class="badge bg-primary">+ Add More</a>
                </label>
                <div id="question_options">
                    @foreach ($question->options as $key => $ques_option)
                        <div class="mb-3 option">
                            <div class="d-flex">
                                <div class="d-flex">
                                    @if ($ques_option->option_img)
                                        <div>
                                            <img src="{{ storage($ques_option->option_img) }}" alt="img"
                                                style="height: 38px;">
                                        </div>
                                    @endif
                                    <input type="file" name="ques_option_img[{{ $key }}]"
                                        class="form-control rounded-0" accept="image/*">
                                    <input type="hidden" name="option_imgs[{{ $key }}]"
                                        value="{{ $ques_option->option_img }}">
                                </div>
                                <div class="flex-fill">
                                    <input type="text" name="ques_option[{{ $key }}]"
                                        class="form-control rounded-0" placeholder="Option Choice {{ $key }}"
                                        value="{{ $ques_option->option_text }}" maxlength="250">
                                </div>

                                <label class="mb-0">
                                    <input type="radio" name="correct_ans" value="{{ $key }}" required=""
                                        {{ $ques_option->correct_ans == true ? 'checked' : '' }}>
                                    <div class="answercheck form-control rounded-0"></div>
                                </label>
                            </div>
                            <a href="javascript:void(0)" class="fw-bold text-danger small remove_option d-block">
                                <i class="fas fa-times"></i> Remove
                            </a>
                        </div>
                    @endforeach
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
                                        value="" maxlength="250">
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
                    $(this).parent().parent().remove();
                });
            });
        </script>
    @endpush
</x-admin.layout>
