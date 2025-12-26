<?php

namespace App\DataTables;

use App\Models\Account;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class AccountDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filter(function ($query) {
                if (request()->has('account_type') && request('account_type') != '') {
                    $query->where('account_type_id', request('account_type'));
                }
                if (request()->has('account_group') && request('account_group') != '') {
                    $query->where('account_group_id', request('account_group'));
                }
                if (request()->has('account_name') && request('account_name') != '') {
                    $query->where('name', 'like', '%' . request('account_name') . '%');
                }
            })
            ->addIndexColumn()
            ->addColumn('type_name', function ($row) {
                return $row->accountType ? $row->accountType->name : 'N/A';
            })
            ->addColumn('group_name', function ($row) {
                return $row->accountGroup ? $row->accountGroup->name : 'N/A';
            })
            ->addColumn('parent_name', function ($row) {
                return $row->parentAccount ? $row->parentAccount->name : 'N/A';
            })
            ->addColumn('balance', function ($row) {
                $balance = Account::getAccountBalance($row->id);
                return '<span class="badge bg-info">' . number_format($balance, 2) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $id = $row->id;
                return view('pages.accounting.accounts.action', compact('id'));
            })
            ->rawColumns(['balance', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Account $model): QueryBuilder
    {
        return $model->with(['accountType:id,name', 'accountGroup:id,name', 'parentAccount:id,name'])->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('accounts-table')
            ->columns($this->getColumns())
            ->parameters([
                'dom' => 'Bftip',
            ])
            ->minifiedAjax()
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
            Column::make('name')->title('Account Name'),
            Column::make('account_number')->title('Account Number'),
            Column::make('type_name')->title('Account Type')->searchable(false)->orderable(false),
            Column::make('group_name')->title('Account Group')->searchable(false)->orderable(false),
            Column::make('parent_name')->title('Parent Account')->searchable(false)->orderable(false),
            Column::make('balance')->title('Balance')->searchable(false)->orderable(false)->addClass('text-end'),
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
        return 'Accounts_' . date('YmdHis');
    }
}
