<x-exam-exam:exam-layout>
    <div class="container pb-5">
        <div class="text-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0n4ODoAIA--3sUNXFOtz78IfoaPwViociyQ&usqp=CAU"
                alt="logo">

            <h2 class="paper_title mb-3">{{ $paper->title }}</h2>
        </div>
        <table class="table table-bordered paper_details mb-5">
            <tbody>
                <tr>
                    <td class="text-end fw-bold w-50">Total Marks</td>
                    <td>{{ $paper->questions_sum_marks }}</td>
                </tr>
                <tr>
                    <td class="text-end fw-bold w-50">Negative Marking</td>
                    <td>{{ $paper->minus_mark_percent ? $paper->minus_mark_percent.'%' : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-end fw-bold w-50">Duration</td>
                    <td>{{ $paper->total_time }} Minutes</td>
                </tr>
                @if($paper->sections_count)
                <tr>
                    <td class="text-end fw-bold w-50">Sections / Groups</td>
                    <td>{{ $paper->sections_count }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-end fw-bold w-50">Total Questions</td>
                    <td>{{ $paper->questions_count }}</td>
                </tr>
            </tbody>
        </table>

        <h3><u>Instructions:</u></h3>
        <div class="instructions">
            {!! $paper->instruction !!}
        </div>
        <hr />
        <div class="text-center mb-5">
            <a href="{{ route('exam.start', [$paper]) }}" class="btn btn-primary px-3" onclick="openFullscreen()">
                Start Exam
            </a>
        </div>
    </div>
</x-exam-exam:exam-layout>
