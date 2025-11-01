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

    <x-exam-exam:user-paper-detail :paper="$paper" :userPaper="$userPaper" />

</x-admin.layout>
