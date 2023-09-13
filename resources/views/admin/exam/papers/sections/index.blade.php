<x-admin.layout>
    <x-admin.breadcrumb title='Question Papers' :links="[
        ['text' => 'Papers', 'url' => route('admin.exam.papers.index')],
        ['text' => 'Show', 'url' => route('admin.exam.papers.show', [$paper])],
        ['text' => 'Sections'],
        ]" :actions="[
        [
            'text' => 'Add Paper',
            'icon' => 'fas fa-plus',
            'url' => route('admin.exam.papers.create'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <form action="{{ route('admin.exam.papers.sections.store', [$paper]) }}" method="POST" class="card shadow-sm">
        @csrf
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
                            <td>
                                <input type="text" class="form-control" name="sections[{{ $section->id }}]"
                                    placeholder="Name of the section" value="{{ $section->name }}" required>
                            </td>
                            <td>{{ $section->questions_count }}</td>
                            <td>
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
        <div class="card-footer">
            <button type="submit" class="btn btn-dark px-3">
                <i class="fas fa-save"></i> Submit
            </button>

            <button type="button" id="add_section" class="btn btn-success px-3">
                <i class="fas fa-plus"></i> Add Section
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            $(document).ready(function() {
                if ($("#paper_sections tbody tr").length == 0) {
                    addRow();
                }

                $("#add_section").click(function(e) {
                    e.preventDefault();
                    addRow();
                });

                $("#paper_sections").on('click', '.remove_section', function() {
                    $(this).parent().parent().remove();
                });

                function addRow() {
                    $("#paper_sections tbody").append(`
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="new_sections[]" placeholder="Name of the section" required>
                            </td>
                            <td></td>
                            <td>
                                <button class="btn btn-sm btn-danger remove_section" title="Delete this item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                }
            });
        </script>
    @endpush
</x-admin.layout>
