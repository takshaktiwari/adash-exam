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
        <div class="col-md-6">
            <form method="POST" action="{{ route('admin.exam.questions.upload.do') }}" enctype="multipart/form-data"
                class="card shadow-sm">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="upload_file" class="form-control" required>
                    </div>
                    <div class="text-danger">
                        <li>File type should be .xlsx (Office 2007 - 365)</li>
                        <li>Max file size should be less than 2MB (2048 KB)</li>
                        <li>Do not upload more than 250 questions at on time.</li>
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
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-dark px-4">
                        <i class="fas fa-save"></i> Submit
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-auto">Instructions</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>
                            <b>UID: </b> UID is for grouping multiple questions together. If UID is not given then question will be added as parent question. If questions has same UID then first question will be parent question and other questions will be child questions. <em>(Only applied for passage / context question)</em>
                        </li>
                        <li>
                            <b>Question: </b> Write your full question here.
                        </li>
                        <li>
                            <b>Context: </b> Context / Passage of the question, If context is added to parent question then this field will be ignored.
                        </li>
                        <li>
                            <b>Answer: </b> Write you correct full descriptive answer.
                        </li>
                        <li>
                            <b>Groups: </b> Write name of groups saperated by pipe sign ('|'). Only enter the valid group name given below: <br />
                            <em>{{ $questionGroups->implode(' | ') }}</em>
                        </li>
                        <li>
                            <b>Option (1 - 5): </b> Enter the option text, remove to column blank if question has not all 5 options.
                        </li>
                        <li>
                            <b>Correct Ans: </b> Enter the correct option number. eg: <em>1</em> , <em>2</em> or <em>3</em>
                        </li>
                        <li>
                            <b>Marks: </b> Enter the marks which this question will carry.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-admin.layout>
