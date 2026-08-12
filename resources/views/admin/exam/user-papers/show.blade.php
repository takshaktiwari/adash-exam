<x-admin.layout>
    <x-admin.breadcrumb title='Create Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Create'],
    ]" :actions="[
        [
            'text' => 'All Exams',
            'icon' => 'fas fa-list',
            'url' => route('admin.exam.user-papers.index'),
            'class' => 'btn-success btn-loader',
        ],
    ]" />

    <div class="card">
        <div class="card-body">
            <x-user-exam-progress :userId="$userPaper->user_id" limit="15" />
        </div>
    </div>

    <x-user-exam-ranking :paper="$paper" :userId="$userPaper->user_id" />

    <x-exam-exam:user-paper-detail :paper="$paper" :userPaper="$userPaper" />

</x-admin.layout>
