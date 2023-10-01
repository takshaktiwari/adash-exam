<x-admin.layout>
    <x-admin.breadcrumb title='Create Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'Filter',
            'icon' => 'fas fa-sliders-h',
            'url' => route('admin.exam.user-papers.index', ['filter' => 1]),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    @if (request('filter'))
        <form action="" class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Paper</label>
                            <select name="paper_id" id="paper_id" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach ($papers as $paper)
                                    <option value="{{ $paper->id }}"
                                        {{ request('paper_id') == $paper->id ? 'selected' : '' }}>
                                        {{ $paper->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">User</label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">-- Select --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="">Started On</label>
                            <input type="date" name="started_on" class="form-control"
                                value="{{ request('started_on') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <input type="hidden" name="filter" value="1">
                <button type="submit" class="btn btn-dark px-4">
                    <i class="fas fa-save"></i> Submit
                </button>
                <a href="{{ url()->current() }}" class="btn btn-danger px-4">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    @endif

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <th>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input check_all_question_ids" type="checkbox">
                                #
                            </label>
                        </div>
                    </th>
                    <th>Paper</th>
                    <th>User</th>
                    <th>Questions</th>
                    <th>Stared At</th>
                    <th>Marks</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    @foreach ($userPapers as $key => $userPaper)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input question_ids" type="checkbox" value="{{ $userPaper->id }}">
                                        {{ $userPapers->firstItem() + $key }}
                                    </label>
                                </div>
                            </td>
                            <td class="small">
                                <a href="{{ route('admin.exam.papers.show', [$userPaper->paper]) }}">
                                    {{ $userPaper->paper->title }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', [$userPaper->user]) }}">
                                    {{ $userPaper->user->name }}
                                </a>
                            </td>
                            <td class="text-nowrap">
                                Total: {{ $userPaper->paper->questions_count }}
                                <span class="px-2 text-dark">|</span>
                                Answered: {{ $userPaper->questions->where('status', 'answered')->count() }}
                            </td>
                            <td class="text-nowrap">{{ $userPaper->start_at?->format('d-M-Y h:i A') }}</td>
                            <td>{{ $userPaper->questions->where('status', 'answered')->sum('marks') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.exam.user-papers.show', [$userPaper]) }}"
                                    class="btn btn-sm btn-info load-circle">
                                    <i class="fas fa-info-circle"></i>
                                </a>
                                <a href="{{ route('admin.exam.user-papers.delete', [$userPaper]) }}"
                                    class="btn btn-sm btn-danger load-circle">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between">
            {{ $userPapers->links() }}

            <div class="actions">
                <button class="btn btn-danger px-3 delete_questions_btn">
                    <i class="fas fa-trash"></i> Delete Checked
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $(".check_all_question_ids").click(function() {
                    $('.question_ids').not(this).prop('checked', this.checked);
                });

                $(".delete_questions_btn").click(function() {
                    var userPaperIds = $(".question_ids:checked").map(function() {
                        return $(this).val();
                    }).get();

                    if(!userPaperIds.length){
                        alert('Please select user papers.');
                        return false;
                    }

                    window.location.href = "{{ route('admin.exam.user-papers.bulk-delete') }}?user_paper_ids="+encodeURIComponent(userPaperIds);
                });
            });
        </script>
    @endpush

</x-admin.layout>
