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
use Illuminate\Support\Facades\DB;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Modules\Contacts\Models\Transaction;

class ContactDataTable extends DataTable
{
    protected $moduleUtil;
    protected $transactionUtil;

    public function __construct(ModuleUtil $moduleUtil, TransactionUtil $transactionUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                 return view('pages.contacts.action', [
                    'row' => $row,
                    'type' => $row->type,
                    'id' => $row->id,
                    'total_due' => $row->due,
                    'total_sell_return' => $row->total_sell_return ?? 0,
                    'sell_return_paid' => $row->sell_return_paid ?? 0,
                    'total_purchase_return' => $row->total_purchase_return ?? 0,
                    'purchase_return_paid' => $row->purchase_return_paid ?? 0,
                    'return_due' => $row->return_due ?? 0,
                    'is_default' => $row->is_default ?? 0,
                    'should_notify' => $row->should_notify ?? 0,
                    'active' => $row->active
                ]);
            })
            ->editColumn('supplier_business_name', function ($row) {
                return $row->supplier_business_name;
            })
             ->editColumn('due', function ($row) {
                $html = '<h5 class="display_currency due" data-currency_symbol="true" data-orig-value="' .  $row->due . '">' . number_format($row->due, 2) . '</h5>';
                return $html;
            })
             ->editColumn('return_due', function ($row) {
                $return_due = $row->return_due ?? ($row->total_sell_return - $row->sell_return_paid);
                $html = '<span class="display_currency return_due" data-currency_symbol="true" data-orig-value="' . $return_due . '">' . number_format($return_due, 2) . '</span>';
                return $html;
            })
             ->editColumn('opening_balance', function ($row) {
                $paidOpeningBalance = !empty($row->opening_balance_paid) ? $row->opening_balance_paid : 0;
                $openingBalance = !empty($row->opening_balance) ? $row->opening_balance : 0;
                $balanceValue = $openingBalance - $paidOpeningBalance;
                // $html = '<span class="display_currency ob" data-currency_symbol="true" data-orig-value="' . $openingBalance . '">' . number_format($openingBalance, 2) . '</span>';
                 $html = '<span class="display_currency ob" data-currency_symbol="true" data-orig-value="' . $balanceValue . '">' . number_format($balanceValue, 2) . '</span>';

                return $html;
            })
             ->editColumn('pay_term', function($row){
                if(!empty($row->pay_term_type) && !empty($row->pay_term_number)){
                    return $row->pay_term_number . ' ' . ucfirst($row->pay_term_type);
                }
                return '';
             })
             ->editColumn('assigned_to', function ($row) {
                return $row->full_name;
            })
            ->editColumn('type', function ($row) {
                return ucfirst($row->type);
            })
            ->editColumn('created_at', function ($row) {
                if (!empty($row->created_at)) {
                    // Check if opening balance transaction exists to prevent showing wrong date
                     $obTransaction = Transaction::where('type', 'opening_balance')->where('contact_id', $row->id)->first();
                    if (!empty($obTransaction)) {
                        return $this->transactionUtil->format_date($obTransaction->transaction_date);
                    }
                    return $this->transactionUtil->format_date($row->created_at);
                }
            })
            ->addColumn('image', function ($row) {
                if(isset($row->image) && $row->image!=null ){
                    $image = url('uploads/media/'.$row->image);
                    return  '<img class="popup" src="'.$image.'" height="50" width="50" >';
                }else{
                    return '';
                }
            })
            ->addColumn('signature', function ($row) {
                if(isset($row->signature) && $row->signature!=null ){
                    $signature = url('uploads/media/'.$row->signature);
                    return  '<img class="popup" src="'.$signature.'" height="50" width="50" >';
                }else{
                    return '';
                }
            })
            ->rawColumns(['action', 'due', 'return_due', 'opening_balance', 'pay_term', 'image', 'signature']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Contact $model): QueryBuilder
    {
        $business_id = request()->session()->get('user.business_id');
        if (empty($business_id)) {
            $business_id = auth()->user()->business_id;
        }
        
        // Ensure business_id is an integer to avoid issues in raw SQL
        $business_id = (int)$business_id;

        $type = request()->get('type');

        if ($type == 'supplier') {
             $query = $model->newQuery()
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->leftjoin('contact_groups AS cg', 'contacts.supplier_group_id', '=', 'cg.id')
                ->leftjoin('user_contact_access AS uca','contacts.id','uca.contact_id')
                ->leftjoin('users','uca.user_id','users.id')
                ->where('contacts.business_id', $business_id)
                ->where('contacts.is_payee', 0)
                ->where(function($q) {
                    $q->where('contacts.type', 'supplier')
                        ->orWhere('contacts.type', 'both');
                })
                ->select([
                    'contacts.contact_id', 'supplier_business_name', 'contacts.active', 'contacts.name', 'cg.name as supplier_group', 'contacts.created_at', 'contacts.mobile', 't.transaction_date',
                    'contacts.type', 'contacts.id', 'uca.user_id','should_notify', 'contacts.is_default',
                    DB::raw("CONCAT(COALESCE(users.firstname, ''),' ',COALESCE(users.middlename, ''),' ',COALESCE(users.lastname,'')) as full_name"),
                    DB::raw("SUM(IF(t.type = 'purchase' AND t.status != 'draft', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase' AND t.status != 'draft', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as purchase_paid"),
                    DB::raw("SUM(IF(t.type = 'purchase' AND t.status != 'draft', final_total, 0) - IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as due"),
                    DB::raw("SUM(IF(t.type = 'purchase_return' AND t.status != 'draft', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return' AND t.status != 'draft', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as purchase_return_paid"),
                    DB::raw("SUM(IF(t.type = 'purchase_return' AND t.status != 'draft', final_total, 0) - IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as return_due"),
                    DB::raw("SUM(IF(t.type = 'opening_balance' AND t.status != 'draft', final_total, 0)) as opening_balance"),
                    DB::raw("SUM(IF(t.type = 'opening_balance' AND t.status != 'draft', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as opening_balance_paid"),
                    'contacts.email', 'contacts.tax_number', 'contacts.pay_term_number', 'contacts.pay_term_type', 'contacts.custom_field1', 'contacts.custom_field2', 'contacts.custom_field3', 'contacts.custom_field4'
                ])
                ->groupBy('contacts.id');
        } else {
             // Customer Query (Default)
              $query = $model->newQuery()
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->leftjoin('contact_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
                ->leftjoin('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->leftjoin('users', 'uca.user_id', 'users.id')
                ->where('contacts.business_id', $business_id)
                ->whereIn('contacts.is_property', [1, 0])
                ->where(function ($q) {
                    $q->where('contacts.type', 'customer')
                        ->orWhere('contacts.type', 'both');
                })
                ->select([
                    'should_notify', 'contacts.contact_id', 'contacts.name', 'contacts.created_at', 'contacts.active',
                    'cg.name as customer_group', 'mobile', 'contacts.id', 'is_default', 'uca.user_id', 'contacts.image', 'contacts.signature', 'supplier_business_name',
                    DB::raw("CONCAT(COALESCE(users.firstname, ''),' ',COALESCE(users.middlename, ''),' ',COALESCE(users.lastname,'')) as full_name"),
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id AND transaction_payments.deleted_at IS NULL), 0)) as sell_return_paid"),
                    'contacts.pay_term_type', 'contacts.pay_term_number', 'contacts.credit_limit', 'contacts.type', 'contacts.email', 'contacts.tax_number',
                    DB::raw("(select sum( if(contact_ledgers.type = 'debit' AND transactions.type = 'opening_balance' AND transactions.business_id=" . $business_id . ",contact_ledgers.amount,0) 
                            + if(contact_ledgers.type = 'debit' AND transactions.type != 'opening_balance' and transactions.business_id=" . $business_id . ", contact_ledgers.amount,0)
                            - if(contact_ledgers.type = 'credit' ,contact_ledgers.amount,0) )
                            from contact_ledgers
                            left join transactions on contact_ledgers.transaction_id=transactions.id
                            where contact_ledgers.contact_id=contacts.id  
                            GROUP BY contact_ledgers.contact_id) as due")
                ])
                ->groupBy('contacts.id');
        }

        return $query;
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
                     ->parameters([
                        'dom'          => 'Bfrtip',
                        'buttons'      => ['excel', 'csv', 'pdf', 'print', 'reset', 'reload'],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
         $columns = [
            Column::make('action')->title('Action')->searchable(false)->orderable(false),
            Column::make('contact_id')->title('Contact ID'),
            Column::make('supplier_business_name')->title('Business Name'),
            Column::make('name')->title('Name'),
            Column::make('email'),
            Column::make('mobile'),
            Column::make('created_at')->title('Added On'),
        ];

        if (request()->get('type') == 'customer') {
            $columns[] = Column::make('customer_group')->title('Customer Group');
            $columns[] = Column::make('credit_limit')->title('Credit Limit');
             $columns[] = Column::make('pay_term')->title('Pay Term');
             $columns[] = Column::make('due')->title('Total Due');
             $columns[] = Column::make('return_due')->title('Total Sell Return Due');
             $columns[] = Column::make('image')->title('Image');
             $columns[] = Column::make('signature')->title('Signature');

        } elseif (request()->get('type') == 'supplier') {
            $columns[] = Column::make('supplier_group')->title('Supplier Group');
            $columns[] = Column::make('pay_term')->title('Pay Term');
            $columns[] = Column::make('due')->title('Total Purchase Due');
            $columns[] = Column::make('return_due')->title('Total Purchase Return Due');
            $columns[] = Column::make('opening_balance')->title('Opening Balance');
        }
        
         $columns[] = Column::make('tax_number')->title('Tax Number');
         $columns[] = Column::make('assigned_to')->title('Assigned to');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Contact_' . date('YmdHis');
    }
}
