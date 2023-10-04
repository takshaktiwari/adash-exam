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
                    @foreach ($questions as $question)
                        <option value="{{ $question->id }}">
                            {{ strip_tags($question->question) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Question <span class="text-danger">*</span></label>
                        <textarea name="question" rows="2" class="form-control text-editor" placeholder="Write your question" >{{ $question->question }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="">Descriptive Answer </label>
                        <textarea name="answer" rows="4" class="form-control text-editor" placeholder="Describe your answer" maxlength="499">{{ $question->answer }}</textarea>
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
                        <div class="d-flex mb-2 option">
                            <div class="flex-fill">
                                <input type="text" name="ques_option[{{ $key }}]"
                                    class="form-control rounded-0" placeholder="Option Choice {{ $key }}"
                                    value="{{ $ques_option->option_text }}" maxlength="250" required="">
                                <a href="javascript:void(0)" class="fw-bold text-danger small remove_option">
                                    <i class="fas fa-times"></i> Remove
                                </a>
                            </div>

                            <label class="mb-0">
                                <input type="radio" name="correct_ans" value="{{ $key }}" required=""
                                    {{ $ques_option->correct_ans == true ? 'checked' : '' }}>
                                <div class="answercheck form-control rounded-0"></div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer">
            <input type="submit" class="btn btn-dark px-4">
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            tinymce.init({
                selector: '.text-editor',
                plugins: 'print preview paste importcss searchreplace autolink autosave directionality code visualblocks visualchars fullscreen image link codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap emoticons',
                imagetools_cors_hosts: ['picsum.photos'],
                menubar: 'file edit view insert format tools table help',
                toolbar: 'bold italic underline strikethrough alignleft aligncenter alignright alignjustify numlist bullist charmap outdent indent | fontselect fontsizeselect formatselect | forecolor backcolor |  emoticons preview link',
                toolbar_sticky: true,
                autosave_ask_before_unload: true,
                height: 250,
                toolbar_mode: 'sliding',
            });
        </script>
        <script>
            $(document).ready(function() {
                $("#add_option_btn").click(function() {
                    var optionsCount = $("#question_options .option").length;
                    var newOptionCount = ++optionsCount;
                    console.log(newOptionCount);
                    $("#question_options").append(`
                        <div class="d-flex mb-2 option">
                            <div class="flex-fill">
                                <input type="text" name="ques_option[${newOptionCount}]"
                                    class="form-control rounded-0"
                                    placeholder="Option Choice ${newOptionCount}"
                                    value="" maxlength="250" required="">
                                <a href="javascript:void(0)" class="fw-bold text-danger small remove_option">
                                    <i class="fas fa-times"></i> Remove
                                </a>
                            </div>

                            <label class="mb-0">
                                <input type="radio" name="correct_ans" value="${newOptionCount}"
                                    required="">
                                <div class="answercheck form-control rounded-0"></div>
                            </label>
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
