<li>
    <a href="{{ route('admin.exam.question-groups.index') }}" class=" waves-effect">
        <i class="fas fa-sticky-note"></i>
        <span>Question Groups</span>
    </a>
</li>
<li>
    <a href="javascript: void(0);" class="has-arrow waves-effect">
        <i class="fas fa-question-circle"></i>
        <span>Question Bank</span>
    </a>
    <ul class="sub-menu mm-collapse" aria-expanded="false">
        <li>
            <a href="{{ route('admin.exam.questions.index') }}">
                All Questions
            </a>
        </li>
        <li>
            <a href="{{ route('admin.exam.questions.create') }}">
                Create Question
            </a>
        </li>
        <li>
            <a href="{{ route('admin.exam.questions.upload') }}">
                Upload Question
            </a>
        </li>
    </ul>
</li>
<li>
    <a href="{{ route('admin.exam.papers.index') }}" class=" waves-effect">
        <i class="fas fa-sticky-note"></i>
        <span>Question Paper</span>
    </a>
</li>
<li>
    <a href="{{ route('admin.exam.user-papers.index') }}" class=" waves-effect">
        <i class="fas fa-sticky-note"></i>
        <span>User Exams / Papers</span>
    </a>
</li>
