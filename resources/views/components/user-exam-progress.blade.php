@if ($userPapers->count())
    <div>
        <h5>Marks (%) Of Last {{ $userPapers->count() }} Exams </h5>
        <canvas id="userExamProgressChart" class="mb-5"></canvas>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            $(document).ready(function() {
                const ctx = document.getElementById('userExamProgressChart');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($labels->reverse()->values()->toArray()),
                        datasets: [{
                            label: 'Marks %',
                            data: @json($userPapers->pluck('percentage')->reverse()->values()->toArray()),
                            backgroundColor: 'rgba(54, 162, 235, 0.5)', // single color for all bars
                            borderWidth: 0
                        }]
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: {
                                ticks: {
                                    callback: function(value) {
                                        // Assuming the label is a date or some text, return first 5 characters
                                        return this.getLabelForValue(value).substring(0, 8) + '...';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endif
