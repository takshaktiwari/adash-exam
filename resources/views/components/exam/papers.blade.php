<div {{ $attributes->merge(['class' => '']) }}>
    @foreach ($papers as $paper)
        <div class="card shadow-sm mb-3">
            <div class="card-body ">
                <div class="row">
                    <div class="col-md-9">
                        <div class="my-2">
                            <h6>{{ $paper->title }}</h6>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span>
                                    <b>Total Marks:</b>
                                    <span>{{ $paper->questions_sum_marks }}</span>
                                </span>
                                @if ($paper->sections_count)
                                    <span class="text-warning px-2">|</span>
                                    <span>
                                        <b>Sections:</b>
                                        <span>{{ $paper->sections_count }}</span>
                                    </span>
                                @endif
                                <span class="text-warning px-2">|</span>
                                <span>
                                    <b>Questions:</b>
                                    <span>{{ $paper->questions_count }}</span>
                                </span>
                                <span class="text-warning px-2">|</span>
                                <span>
                                    <b>Time:</b>
                                    <span>{{ $paper->total_time }} Mins</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 my-auto">
                        @if ($paper->security_code)
                            <a href="javascript:void(0)"
                                class="btn btn-sm btn-info px-3 {{ !$paper->hasAttempts() ? 'disabled' : '' }}"
                                data-bs-toggle="modal" data-toggle="modal"
                                data-bs-target="#security_modal_{{ $paper->id }}"
                                data-target="#security_modal_{{ $paper->id }}">
                                Start Exam <i class="fas fa-arrow-right"></i>
                            </a>
                        @else
                            <a href="{{ route('exam.instructions', [$paper]) }}"
                                class="btn btn-sm btn-info px-3 {{ !$paper->hasAttempts() ? 'disabled' : '' }}">
                                Start Exam <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                        <a href="{{ route('exam.user-papers.index', [$paper]) }}" class=" small">
                            <b>Attempts:</b>
                            <span>{{ $paper->user_papers_count }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if ($paper->security_code)
            <div class="modal" id="security_modal_{{ $paper->id }}">
                <div class="modal-dialog">
                    <form hx-boost="false" action="{{ route('exam.authenticate', [$paper]) }}" class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h4 class="modal-title">Enter Security Key</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <!-- Modal body -->
                        <div class="modal-body">
                            <input type="text" class="form-control" name="security_code" required>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-dark px-3">
                                <i class="fas fa-save"></i> Submit
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        </div>

                    </form>
                </div>
            </div>
        @endif
    @endforeach
</div>
