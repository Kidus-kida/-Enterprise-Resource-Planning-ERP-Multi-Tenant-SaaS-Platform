<?php

namespace App\DataTables;

use Modules\Contacts\Models\Transaction;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Carbon\Carbon;

class PurchaseDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                $html = '<div class="dropdown dropdown-action text-end">
                            <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="' . route('purchase.show', $row->id) . '"><i class="fa-solid fa-eye m-r-5"></i> View</a>
                                <a class="dropdown-item" href="' . route('purchase.edit', $row->id) . '"><i class="fa-solid fa-pencil m-r-5"></i> Edit</a>
                                <a class="dropdown-item delete-purchase" href="javascript:void(0);" data-href="' . route('purchase.destroy', $row->id) . '"><i class="fa-solid fa-trash m-r-5"></i> Delete</a>
                            </div>
                        </div>';
                return $html;
            })
            ->editColumn('transaction_date', function($row) {
                return Carbon::parse($row->transaction_date)->format("Y-m-d");
            })
            ->editColumn('status', function ($row) {
                $status = ucfirst($row->status);
                $class = 'bg-info';
                if ($row->status == 'received') $class = 'bg-success';
                elseif ($row->status == 'pending') $class = 'bg-warning';
                elseif ($row->status == 'ordered') $class = 'bg-primary';
                return '<span class="badge ' . $class . '">' . $status . '</span>';
            })
            ->editColumn('payment_status', function ($row) {
                $status = ucfirst($row->payment_status);
                $class = 'bg-info';
                if ($row->payment_status == 'paid') $class = 'bg-success';
                elseif ($row->payment_status == 'due') $class = 'bg-danger';
                elseif ($row->payment_status == 'partial') $class = 'bg-warning';
                return '<span class="badge ' . $class . '">' . $status . '</span>';
            })
            ->editColumn('final_total', function ($row) {
                return number_format($row->final_total, 2);
            })
            ->addColumn('due', function ($row) {
                $paid = is_numeric($row->amount_paid) ? $row->amount_paid : 0;
                return number_format($row->final_total - $paid, 2);
            })
            ->editColumn('amount_paid', function($row) {
                return number_format($row->amount_paid, 2);
            })
            ->rawColumns(['status', 'payment_status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): QueryBuilder
    {
        $business_id = request()->session()->get('user.business_id') ?? 1;
        
        $query = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'purchase')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('business_locations', 'transactions.location_id', '=', 'business_locations.id')
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.ref_no',
                'transactions.status',
                'transactions.payment_status',
                'transactions.final_total',
                'contacts.name as supplier_name',
                'business_locations.name as location_name',
                DB::raw('COALESCE((SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id = transactions.id AND transaction_payments.deleted_at IS NULL), 0) as amount_paid')
            );

        // Filtering
        if (!empty(request()->supplier_id)) {
            $query->where('transactions.contact_id', request()->supplier_id);
        }
        if (!empty(request()->location_id)) {
            $query->where('transactions.location_id', request()->location_id);
        }
        if (!empty(request()->status)) {
            $query->where('transactions.status', request()->status);
        }
        if (!empty(request()->payment_status)) {
            $query->where('transactions.payment_status', request()->payment_status);
        }
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $query->whereDate('transactions.transaction_date', '>=', request()->start_date)
                  ->whereDate('transactions.transaction_date', '<=', request()->end_date);
        }

        return $query->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('purchase-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0)
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
            Column::make('transaction_date')->title(__('Date')),
            Column::make('ref_no')->title(__('Reference No')),
            Column::make('location_name')->title(__('Location')),
            Column::make('supplier_name')->title(__('Supplier')),
            Column::make('status')->title(__('Status')),
            Column::make('payment_status')->title(__('Payment Status')),
            Column::make('final_total')->title(__('Grand Total')),
            Column::make('amount_paid')->title(__('Paid'))->searchable(false),
            Column::computed('due')->title(__('Due')),
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
        return 'Purchase_' . date('YmdHis');
    }
}
