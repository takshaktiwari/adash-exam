<x-admin.layout>
    <x-admin.breadcrumb title='Question Papers' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Show'],
    ]" :actions="[
        [
            'text' => 'Add Paper',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
        [
            'text' => 'All Paper',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.papers.index'),
            'class' => 'btn-dark btn-loader',
        ],
    ]" />

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-auto">{{ $paper->title }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <th>Title</th>
                                <td>{{ $paper->title }}</td>
                            </tr>
                            <tr>
                                <th>Minus Marking</th>
                                <td>{{ $paper->minus_mark_percent }} %</td>
                            </tr>
                            <tr>
                                <th>Duration</th>
                                <td>{{ $paper->total_time }}</td>
                            </tr>
                            <tr>
                                <th>Total Questions</th>
                                <td>{{ $paper->questions_count }}</td>
                            </tr>
                            <tr>
                                <th>Activate At</th>
                                <td>{{ $paper->activate_at->format('d-M-Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Expire At</th>
                                <td>{{ $paper->expire_at->format('d-M-Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $paper->created_at->format('d-M-Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ $paper->status() }}</td>
                            </tr>
                            <tr>
                                <th>Shuffle Questions</th>
                                <td>{{ $paper->shuffle_questions ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Security Code</th>
                                <td>{{ $paper->security_code }}</td>
                            </tr>
                            <tr>
                                <th>Attempts Limit</th>
                                <td>{{ $paper->attempts_limit }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <div>
                        <a href="{{ route('exam.instructions', [$paper]) }}" class="btn btn-info load-circle"
                            title="Start Paper" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Start Exam
                        </a>
                        @if (!$paper->sections->count())
                            <a href="{{ route('admin.exam.papers.questions.edit', [$paper]) }}"
                                class="btn btn-info px-3" title="Questions">
                                <i class="fas fa-question-circle"></i> Questions
                            </a>
                        @endif
                    </div>


                    <div>
                        <a href="{{ route('admin.exam.papers.edit', [$paper]) }}" class="btn btn-success px-3"
                            title="Edit Paper">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.exam.papers.destroy', [$paper]) }}" class="d-inline-block"
                            method="POST">
                            @csrf
                            @method('DELETE')
                            <button href="" class="btn btn-danger px-3 delete-alert" title="Delete Date Slot">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if ($paper->sections->count())
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="my-auto">Question Sections / Groups</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table" id="paper_sections">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Questions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paper->sections as $section)
                                    <tr>
                                        <td>{{ $section->name }}</td>
                                        <td>{{ $section->questions_count }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.exam.papers.sections.show', [$paper, $section]) }}"
                                                class="btn btn-sm btn-primary load-circle" title="Paper Questions">
                                                <i class="far fa-question-circle"></i>
                                            </a>
                                            <a href="{{ route('admin.exam.papers.sections.destroy', [$paper, $section]) }}"
                                                class="btn btn-sm btn-danger delete-alert" title="Delete Date Slot">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="my-auto">Instruction</h5>
        </div>
        <div class="card-body table-responsive">
            {!! $paper->instruction !!}
        </div>
    </div>
</x-admin.layout>
