<?php

namespace Modules\Contacts\DataTables;

use Modules\Contacts\Models\SupplierProductMapping;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class SupplierMappingDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
             ->addColumn('supplier_name', function ($row) {
                 return $row->supplier ? $row->supplier->name : '';
            })
             ->addColumn('product_name', function ($row) {
                 // return $row->product ? $row->product->name : '';
                 // Placeholder until Product model exists or if it exists
                 return 'Product #' . $row->product_id; 
            })
            ->editColumn('updated_at', function ($row) {
                return format_date($row->updated_at);
            })
            ->addColumn('action', function ($row) {
                return view('contacts::supplier_mappings.action', ['id' => $row->id]);
            })->rawColumns(['action']);
    }

    public function query(SupplierProductMapping $model): QueryBuilder
    {
        return $model->newQuery()->with('supplier'); // .with('product')
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('supplier-mapping-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0, 'desc')
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('supplier_name')->title('Supplier'),
            Column::make('product_name')->title('Product'),
             Column::make('updated_at')->title('Updated At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-end'),
        ];
    }

    protected function filename(): string
    {
        return 'SupplierMapping_' . date('YmdHis');
    }
}
