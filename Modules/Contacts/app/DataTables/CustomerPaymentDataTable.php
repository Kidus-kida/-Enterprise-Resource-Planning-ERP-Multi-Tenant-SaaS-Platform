<?php

namespace Modules\Contacts\DataTables;

use Modules\Contacts\Models\TransactionPayment;
use App\Models\Contact; // Added this import
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class CustomerPaymentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('paid_on', function ($row) {
                return format_date($row->paid_on);
            })
             ->editColumn('amount', function ($row) {
                return number_format($row->amount, 2);
            })
            ->editColumn('method', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->method));
            })
             ->addColumn('payment_ref_no', function ($row) {
                return $row->payment_ref_no;
            })
             ->addColumn('contact_name', function ($row) {
                 // Try to get contact from payment directly or via transaction
                 if($row->payment_for){
                      $contact = \App\Models\Contact::find($row->payment_for);
                      return $contact ? $contact->name : '';
                 }
                 return $row->transaction && $row->transaction->contact ? $row->transaction->contact->name : '';
            })
            ->addColumn('action', function ($row) {
                return view('pages.customer_payments.action', ['id' => $row->id]);
            })->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TransactionPayment $model): QueryBuilder
    {
        // Logic: Payments made by Customers.
        // Usually these are payments linked to a 'sell' transaction OR 'opening_balance' for customer
        // OR directly linked to customer (advance).
        // For simplicity, we query payments where the related transaction is a SELL or Loan, OR the payment_for is a customer.
        
        return $model->newQuery()
                    ->with(['transaction.contact', 'contact'])
                    ->select('transaction_payments.*');
                    // Add more strict filtering if needed to separate Supplier payments
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('customer-payment-table')
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

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('paid_on')->title('Date'),
            Column::make('payment_ref_no')->title('Ref No'),
            Column::make('customer_name')->title('Customer'),
            Column::make('amount')->title('Amount'),
            Column::make('method')->title('Method'),
            Column::make('note')->title('Note'),
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
        return 'CustomerPayment_' . date('YmdHis');
    }
}
