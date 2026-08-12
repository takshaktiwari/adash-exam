<x-admin.layout>
    <x-admin.breadcrumb title='Questions' :links="[['text' => 'Dashboard', 'url' => route('admin.dashboard')], ['text' => 'Questions']]" :actions="[
        [
            'text' => 'User Exams',
            'icon' => 'fas fa-sliders-h',
            'url' => route('admin.exam.user-papers.index'),
            'class' => 'btn-dark btn-loader',
        ],
    ]" />

    <div class="row">
        <div class="col-md-6 col-sm-10">
            <form action="" id="submit_user_id" class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-auto">Select User</h5>
                </div>
                <div class="card-body">
                    <select name="user_id" id="user_id" class="form-control select2" required
                        onchange="document.getElementById('submit_user_id').submit()">
                        <option value="">-- Select User --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if ($selectedUser)
        <div class="card">
            <div class="card-body">
                <x-user-exam-progress :userId="$selectedUser->id" limit="15" />
            </div>
        </div>
    @endif

</x-admin.layout>
