<?php

namespace Modules\Contacts\DataTables;

use Modules\Contacts\Models\ContactGroup;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\View;

class ContactGroupDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 2); 
            })
            ->editColumn('created_at', function ($row) {
                return format_date($row->created_at);
            })
            ->addColumn('action', function ($row) {
                return view('contacts::contact_groups.action', ['id' => $row->id, 'name' => $row->name]);
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ContactGroup $model): QueryBuilder
    {
        $business_id = auth()->user()->business_id;
        $type = request()->get('type');

        $query = $model->newQuery()
            ->where('contact_groups.business_id', $business_id)
            ->select([
                'contact_groups.id',
                'contact_groups.name', 
                'contact_groups.type', 
                'contact_groups.amount',
                'contact_groups.account_type_id',
                'contact_groups.interest_account_id', 
                'contact_groups.created_at'
            ]);

        if (!empty($type)) {
            $query->where(function ($q) use ($type) {
                 $q->where('contact_groups.type', $type)
                   ->orWhere('contact_groups.type', 'both');
            });
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('contact-group-table')
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
            Column::make('name')->title(__('Name')),
            Column::make('type')->title(__('Type')),
            Column::make('amount')->title(__('Amount')),
            Column::make('created_at')->title(__('Created At')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-end'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ContactGroup_' . date('YmdHis');
    }
}
