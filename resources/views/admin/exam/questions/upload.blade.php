<x-admin.layout>
    <x-admin.breadcrumb title='Upload Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Upload'],
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

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.exam.questions.upload.do') }}" enctype="multipart/form-data"
                        class="p-sm-4 p-1">
                        @csrf
                        <div class="form-group">
                            <label for="">Select File <span class="text-danger">*</span></label>
                            <input type="file" name="upload_file" class="form-control" required>
                        </div>
                        <div class="mb-4 text-danger">
                            <li>File type should be .xlsx (Office 2007 - 365)</li>
                            <li>Max file size should be less than 2MB (2048 KB)</li>
                            <li>Excelsheet should be contain 200 products at a time</li>
                            <li>Columns headings should be same as given in example file</li>
                            <li>
                                <a href="{{ route('admin.exam.questions.sample-download') }}"
                                    class="text-primary font-weight-bold font-size-16">
                                    <i class="fas fa-download"></i>
                                    Download Sample File
                                </a>
                            </li>
                        </div>
                        <input type="submit" class="btn btn-dark px-4">
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
