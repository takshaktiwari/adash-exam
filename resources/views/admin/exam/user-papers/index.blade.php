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
            {{ $dataTable->table() }}
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush

</x-admin.layout>
