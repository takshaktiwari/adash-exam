<x-admin.layout>
    <x-admin.breadcrumb title='Create Question' :links="[
        ['text' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['text' => 'Questions', 'url' => route('admin.exam.questions.index')],
        ['text' => 'Create'],
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

    @push('styles')
    <style>
		/* HIDE RADIO */
		[type=radio] {
		  	position: absolute;
		  	opacity: 0!important;
		  	visibility: hidden;
		  	width: 0;
		  	height: 0;
		}

		/* IMAGE STYLES */
		[type=radio] + .answercheck {
		  	cursor: pointer;
		  	width: 40px;
		  	background-color: #ececec;
		}

		[type=radio] + .answercheck:after{
			content: '\f00d';
			font-family: 'Font Awesome 5 Free';
			font-weight: 800;
			font-size: 16px;
			color: #b9b9b9;
		}

		/* CHECKED STYLES */
		[type=radio]:checked + .answercheck:after {
		  	content: '\f00c';
		  	font-family: 'Font Awesome 5 Free';
		  	color: #333;
		}
	</style>
    @endpush


    <div class="row">
        <div class="col-md-7">
            <form method="POST" action="{{ route('admin.exam.questions.update', [$question]) }}" class="card shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-body">
					<div class="form-group">
						<label for="">Question <span class="text-danger">*</span></label>
						<textarea name="question" rows="2" class="form-control" placeholder="Write your question" required="">{{ $question->question }}</textarea>
					</div>
                    <div class="form-group">
                        <label for="">Marks <span class="text-danger">*</span></label>
                        <input type="number" name="marks" class="form-control" required placeholder="eg. 2" value="{{ $question->marks }}" >
                    </div>
					<div class="form-group">
						<label for="">Question Choices <span class="text-danger">*</span></label>
						@foreach($question->options as $key => $ques_option)
							<div class="d-flex mb-2">
								<div class="flex-fill">
									<input type="text" name="ques_option[{{ $key }}]" class="form-control rounded-0" placeholder="Option Choice {{ $key }}" value="{{ $ques_option->option_text }}" maxlength="250" required="">
								</div>

								<label class="mb-0">
								  	<input type="radio" name="correct_ans" value="{{ $key }}" required="" {{ ($ques_option->correct_ans == true)? 'checked' : '' }}>
								  	<div class="answercheck form-control rounded-0"></div>
								</label>
							</div>
						@endforeach
					</div>
					<div class="form-group">
						<label for="">Descriptive Answer </label>
						<textarea name="answer" rows="4" class="form-control" placeholder="Describe your answer" maxlength="499">{{ $question->answer }}</textarea>
					</div>
                    <div class="form-group">
                        <label for="">Question Groups <span class="text-danger">*</span></label>
                        <select name="question_group_id[]" id="question_group_id" class="form-control select2" multiple required>
                            <option value="">-- Select --</option>
                            @foreach ($questionGroups as $group)
                                <option value="{{ $group->id }}" @selected($question->questionGroups->pluck('id')->contains($group->id)) >
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
				</div>
				<div class="card-footer">
					<input type="submit" class="btn btn-dark px-4">
				</div>
            </form>
        </div>
    </div>
</x-admin.layout>
