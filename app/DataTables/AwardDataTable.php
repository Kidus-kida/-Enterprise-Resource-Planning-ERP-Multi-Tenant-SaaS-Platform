<?php

namespace App\DataTables;

use App\Models\Award;
use App\Enums\UserType;
use Spatie\Menu\Laravel\Html;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;

class AwardDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method. 
     */
    public function dataTable($query)
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            

            ->addColumn('user', function ($row) {
                return $row->user->fullname ?? $row->user->name ?? '';
            })

            ->editColumn('awarded_by', function ($row) {
                return $row->awarded_by ?? '';
            })
            ->editColumn('title', function ($row) {
                return $row->title;
            })


            ->editColumn('description', function ($row) {
                $full = str_replace('&nbsp;', ' ', strip_tags($row->description ?? ''));
                $short = Str::limit($full, 30, '...');
                return '<span title="' . e($full) . '">' . e($short) . '</span>';
            })


            ->editColumn('award_type', function ($row) {
                return $row->award_type ?? '';
            })

            ->editColumn('awarded_at', function ($row) {
                return format_date($row->awarded_at);
            })

            ->editColumn('award_file', function ($row) {
                if (!empty($row->award_file)) {
                    $filePath = asset('storage/' . $row->award_file);
                    return '<a href="' . $filePath . '" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fa fa-eye"></i> View File
                            </a>';
                }
                return '<span class="text-muted">No File</span>';
            })

            ->editColumn('row_actions', function ($row) {
                $editUrl = route('awards.edit', $row->id); // AJAX modal load
                $deleteUrl = route('awards.destroy', $row->id);

                return '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="javascript:void(0)" class="dropdown-item" data-url="' . $editUrl . '" data-title="Edit Award" data-ajax-modal="true" data-size="lg">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </li>
                            <li>
                                <form method="POST" action="' . $deleteUrl . '" onsubmit="return confirm(\'Are you sure you want to delete this award?\');">
                                    ' . csrf_field() . method_field('DELETE') . '
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                ';
            })

            ->rawColumns(['user', 'award_file', 'row_actions', 'description']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query()
    {
        if (auth()->user()->type === UserType::SUPERADMIN) {
            return Award::query();
        }
        if (route_is('assigned-tickets')) {
            return Award::where('user_id', auth()->user()->id)
                ->where('created_by', '!=', auth()->user()->id)
                ->newQuery();
        }
        return Award::where('created_by', auth()->user()->id)->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('ticket-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex') // This is for serial number
                ->title('S/N')
                ->addClass('text-center')
                ->searchable(false)
                ->orderable(false),

            Column::make('user')->title('Recipient')
                ->addClass('text-center'),
            
            Column::make('awarded_by')->title('Awarded By'),
            Column::make('title')->title('Title'),
            Column::make('description')->title('Description'),
            Column::make('award_type')->title('Award Type'),
            Column::make('awarded_at')->title('Awarded At'),
            Column::make('award_file')->title('Award File'),
            Column::computed('row_actions')
                ->title('Action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),

        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Award_' . date('YmdHis');
    }
}
