<?php

namespace Takshak\Exam\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Takshak\Exam\Models\UserPaper;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserPapersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', fn($item) => $item->created_at->format('Y-m-d h:i A'))
            ->editColumn('start_at', fn($item) => $item->start_at->format('Y-m-d h:i A'))
            ->editColumn('answered_questions_count', function ($item) {
                return $item->questions->where('status', 'answered')->count();
            })
            ->editColumn('marks', function ($item) {
                return $item->questions->where('status', 'answered')->sum('marks');
            })
            ->addColumn('checkbox', function ($cart) {
                return '
                    <div class="form-check">
                        <label class="form-check-label mb-0">
                            <input class="form-check-input selected_items" type="checkbox" name="selected_items[]" value="' . $cart->id . '">
                        </label>
                    </div>
                ';
            })
            ->addColumn('action', function ($item) {
                $html = '';

                $html .= view('components.admin.btns.action-show', [
                    'url' => route('admin.exam.user-papers.show', [$item])
                ]);

                $html .= view('components.admin.btns.action-delete', [
                    'url' => route('admin.exam.user-papers.delete', [$item])
                ]);

                return $html;
            })
            ->rawColumns(['action', 'checkbox', 'created_at']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserPaper $model): QueryBuilder
    {
        return $model->query()
            // ->where('id', 5122)
            ->with('user:id,name')
            ->with(['paper' => function ($query) {
                $query->withCount('questions');
            }])
            ->with('questions:id,user_paper_id,status,marks')
            ->when(request('paper_id'), function ($query) {
                $query->where('paper_id', request('paper_id'));
            })
            ->when(request('user_id'), function ($query) {
                $query->where('user_id', request('user_id'));
            })
            ->when(request('started_on'), function ($query) {
                $query->whereDate('start_at', request('started_on'));
            })->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('userpapers-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"d-flex mb-2 justify-content-between flex-wrap gap-3"<"d-flex gap-3"lB>f>rt<"d-flex justify-content-between flex-wrap gap-3 mt-3"ip>')
            ->selectStyleSingle()
            ->responsive(true)
            ->pageLength(100)
            ->serverSide(true) // Enable server-side processing
            ->processing(true)
            ->stateSave(true)
            ->buttons([
                // Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                // Button::make('print'),
                // Button::make('reset'),
                Button::make('reload'),
                Button::raw([
                    'extend' => 'colvis',
                    'text' => '<i class="fas fa-columns"></i>',
                    'className' => 'btn btn-secondary btn-sm'
                ]),
                Button::raw('deleteItems')
                    ->text('<i class="bi bi-archive" title="Delete Items"></i>')
                    ->addClass('bg-danger text-white')
                    ->action("
                        let selectedValues = [];
                        $('.selected_items:checked').each(function() {
                            selectedValues.push($(this).val());
                        });

                        if(!selectedValues.length) {
                            alert('Please select at least one item.');
                            return false;
                        }

                        if(!confirm('Are you sure?')) {
                            return false;
                        }

                        let baseUrl = '" . route('admin.exam.user-papers.bulk-delete') . "';
                        let params = selectedValues.map(value => `item_ids[]=`+value).join('&');
                        let fullUrl = baseUrl+`?`+params;

                        window.location.href = fullUrl;
                    "),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('checkbox')
                ->title('
                    <div class="form-check">
                        <label class="form-check-label mb-0">
                            <input class="form-check-input" type="checkbox" id="check_all_items" value="1">
                        </label>
                    </div>
                ')
                ->searchable(false)
                ->orderable(false)
                ->exportable(false)
                ->printable(true)
                ->width(20)
                ->addClass('text-center'),

            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center text-nowrap'),

            Column::make('paper.title')->title('Paper'),
            Column::make('user.name')->title('User'),
            Column::make('paper.questions_count')->title('Ques.')
                ->orderable(false)->sortable(false)->searchable(false),
            Column::make('answered_questions_count')->title('Ans. Ques.')
                ->orderable(false)->sortable(false)->searchable(false),
            Column::make('start_at'),
            Column::make('marks')->orderable(false)->sortable(false)->searchable(false),
            Column::make('created_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UserPapers_' . date('YmdHis');
    }
}
