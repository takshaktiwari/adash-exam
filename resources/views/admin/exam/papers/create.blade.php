<x-admin.layout>
    <x-admin.breadcrumb title='Question Paper Create' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Question Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'All Papers',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.papers.index'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <form method="POST" action="{{ route('admin.exam.papers.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label for="">Paper title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required placeholder="Question Title"
                    value="{{ old('title') }}">
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Total Time <span class="text-danger">*</span></label>
                        <div class="input-group mb-3">
                            <input type="number" name="total_time" class="form-control" required placeholder="eg. 100"
                                value="{{ old('total_time') }}">
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
                            value="{{ old('activate_at') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Expire At<span class="text-danger">*</span></label>
                        <input type="datetime-local" name="expire_at" class="form-control" required
                            value="{{ old('expire_at') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Status<span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Negative Marks <span class="small">(in %)</span></label>
                        <div class="input-group mb-3">
                            <input type="number" name="minus_mark_percent" class="form-control" placeholder="eg. 25"
                                value="{{ old('minus_mark_percent') }}">
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
                                <input class="form-check-input" type="checkbox" id="has_sections" name="has_sections"
                                    value="1" checked>
                                <span>Is this paper has question sections / groups</span>
                            </label>
                        </div>

                        <div class="form-check ms-3" id="lock_sections_checkbox" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" id="lock_sections" name="lock_sections"
                                    value="1">
                                <span class="d-block">
                                    Lock sections
                                    <span class="small d-block fw-normal">Other section will only be active when first
                                        one completed</span>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Shuffle Questions<span class="text-danger">*</span></label>
                        <select name="shuffle_questions" class="form-control" required>
                            <option value="">-- Select --</option>
                            <option value="1">Shuffle</option>
                            <option value="0">Don't Shuffle</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Has Security Code (optional)</label>
                        <input type="text" name="security_code" value="{{ old('security_code') }}"
                            class="form-control">
                        <span class="small">Security code need to be entered to start the exam if has code</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="">Attempts Limit (optional)</label>
                        <input type="number" name="attempts_limit" value="{{ old('attempts_limit') }}"
                            class="form-control">
                        <span class="small">Limit the attemts a user will get. Blank will be unlimited.</span>
                    </div>
                </div>
            </div>
            <div class="">
                <label for="">Question Paper Instruction <span class="text-danger">*</span></label>
                <textarea name="instruction" rows="4" class="form-control text-editor">{{ old('instruction') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-dark px-4">
                <i class="fas fa-save"></i> Submit
            </button>
        </div>
    </form>

    @push('scripts')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <script>
            lockSections();
            $("#has_sections").change(() => lockSections());

            function lockSections() {
                if ($("#has_sections").is(':checked')) {
                    $("#lock_sections_checkbox").fadeIn('fast')
                } else {
                    $("#lock_sections_checkbox").fadeOut('fast')
                }
            }

            $(document).ready(function() {
                $('.text-editor').summernote({
                    placeholder: 'Write instructions here',
                    tabsize: 4,
                    height: 200
                });
            });
        </script>
    @endpush
</x-admin.layout>
