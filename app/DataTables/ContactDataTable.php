<?php

namespace App\DataTables;

use Modules\Contacts\Models\Contact;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class ContactDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('supplier_business_name', function ($row) {
                return $row->supplier_business_name;
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('created_at', function ($row) {
                if (!empty($row->created_at)) {
                    return format_date($row->created_at);
                }
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                return view('pages.contacts.action', compact(
                    'id'
                ));
            })->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Contact $model): QueryBuilder
    {
        return $model->newQuery()
            ->when(request()->has('type') && in_array(request('type'), ['customer', 'supplier']), function ($q) {
                return $q->where('type', request('type'));
            });
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('contact-table')
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
            Column::make('contact_id')->title('contact ID'),
            Column::make('name')->title('Name'),
            Column::make('supplier_business_name')->title('Business Name'),
            Column::make('email'),
            Column::make('mobile'),
             Column::make('supplier group'),
            Column::make('assign to'),
            Column::make('pay to'),
             Column::make('pay term'),
             Column::make('total purchase due'),
            Column::make('total purchase return due'),
            Column::make('opening balance'),
            Column::make('tax number'),
            Column::make('adeded on')->title('Added On')->data('created_at'),
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
        return 'Contact_' . date('YmdHis');
    }
}
