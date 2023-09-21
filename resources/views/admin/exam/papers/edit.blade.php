<x-admin.layout>
    <x-admin.breadcrumb title='Question Paper Create' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Question Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'Create Papers',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
        [
            'text' => 'All Papers',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.papers.index'),
            'class' => 'btn-dark btn-loader',
        ],
    ]" />

    <form method="POST" action="{{ route('admin.exam.papers.update', [$paper]) }}" class="card shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="">Paper title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="Question Title"
                    value="{{ old('title', $paper->title) }}">
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Total Time <span class="text-danger">*</span></label>
                        <div class="input-group mb-3">
                            <input type="number" name="total_time" class="form-control" required placeholder="eg. 100"
                                value="{{ old('total_time', $paper->total_time) }}">
                            <div class="input-group-append">
                                <span class="input-group-text">Mins</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Activate At <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="activate_at" class="form-control" required
                            value="{{ old('activate_at', $paper->activate_at) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Expire At<span class="text-danger">*</span></label>
                        <input type="datetime-local" name="expire_at" class="form-control" required
                            value="{{ old('expire_at', $paper->expire_at) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Status<span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="1" {{ ($paper->status == '1') ? 'selected' : '' }} >Active</option>
                            <option value="0" {{ ($paper->status == '0') ? 'selected' : '' }} >Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Negative Marks <span class="small">(in %)</span></label>
                        <div class="input-group mb-3">
                            <input type="number" name="minus_mark_percent" class="form-control" placeholder="eg. 25"
                                value="{{ old('minus_mark_percent', $paper->minus_mark_percent) }}">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-auto">
                    <div class="form-group ">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" id="has_sections" name="has_sections" value="1" {{ $paper->sections->count() ? 'checked' : '' }}>
                                <span>Is this paper has question sections / groups</span>
                            </label>
                          </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Shuffle Questions<span class="text-danger">*</span></label>
                        <select name="shuffle_questions" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="1" {{ ($paper->shuffle_questions == '1') ? 'selected' : '' }} >Shuffle</option>
                            <option value="0" {{ ($paper->shuffle_questions == '0') ? 'selected' : '' }} >Don't Shuffle</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Has Security Code (optional)</label>
                        <input type="text" name="security_code" value="{{ old('security_code', $paper->security_code) }}" class="form-control">
                        <span class="small">Security code need to be entered to start the exam if has code</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Attempts Limit (optional)</label>
                        <input type="number" name="attempts_limit" value="{{ old('attempts_limit', $paper->attempts_limit) }}" class="form-control">
                        <span class="small">Limit the attemts a user will get. Blank will be unlimited.</span>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="">Question Paper Instruction <span class="text-danger">*</span></label>
                <textarea name="instruction" rows="4" class="form-control text-editor">{{ old('instruction', $paper->instruction) }}</textarea>
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
                toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview print | insertfile image link codesample',
                toolbar_sticky: true,
                autosave_ask_before_unload: true,
                height: 400,
                toolbar_mode: 'sliding',
            });
        </script>
    @endpush
</x-admin.layout>
