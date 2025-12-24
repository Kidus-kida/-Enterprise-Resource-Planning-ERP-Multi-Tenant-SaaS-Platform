<div class="btn-group">
    <button type="button" class="btn btn-info dropdown-toggle btn-xs"
            data-bs-toggle="dropdown" aria-expanded="false">
        Actions
        <span class="caret"></span><span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-left" role="menu">
        @if($type == 'customer')
            @if($total_due > 0)
                <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getPayContactDue'], [$id])}}?type=sell" data-ajax-modal="true" data-title="Pay Due Amount" class="pay_sale_due"><i class="fa fa-credit-card" aria-hidden="true"></i> Pay Due Amount</a></li>
            @endif
            @if(($total_sell_return - $sell_return_paid)  > 0 && $total_due < 0)
                <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getPayContactDue'], [$id])}}?type=sell_return" data-ajax-modal="true" data-title="Pay Sell Return Due" class="pay_purchase_due"><i class="fa fa-credit-card" aria-hidden="true"></i> Pay Sell Return Due</a></li>
            @endif
        @elseif($type == 'supplier')
            @if($total_due > 0)
                <li><a href="javascript:void(0)" data-url="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getPayContactDue'], [$id]) }}?type=purchase" data-ajax-modal="true" data-title="@lang("contact.pay_due_amount")" class="pay_purchase_due"><i class="fa fa-credit-card" aria-hidden="true"></i>@lang("contact.pay_due_amount")</a></li>
            @endif
            @if(($total_purchase_return - $purchase_return_paid) > 0 && $total_due < 0)
                <li><a href="javascript:void(0)" data-url="{{ action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getPayContactDue'], [$id]) }}?type=purchase_return" data-ajax-modal="true" data-title="Receive Purchase Return Due" class="pay_purchase_due"><i class="fa fa-credit-card" aria-hidden="true"></i>Receive Purchase Return Due</a></li>
            @endif
        @endif

        <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getAdvancePayment'], [$id])}}?type=advance_payment" data-ajax-modal="true" data-title="Advance Payment" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i> Advance Payment</a></li>
        
        <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getDirectLoan'], [$id])}}" data-ajax-modal="true" data-title="Direct Loan" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i> Direct Loan</a></li>
        
        <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getRefundDeposit'], [$id])}}" data-ajax-modal="true" data-title="Refund Deposit" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i> Refund Deposit</a></li>
        
        <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getSecurityDeposit'], [$id])}}?type=security_deposit" data-ajax-modal="true" data-title="Security Deposit" class="pay_purchase_due"><i class="fa fa-shield" aria-hidden="true"></i> Security Deposit</a></li>
        
        <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'getRefundPayment'], [$id])}}?type=refund_payment" data-ajax-modal="true" data-title="Refund/Cheque Return" class="pay_purchase_due"><i class="fa fa-recycle" aria-hidden="true"></i> Refund/Cheque Return</a></li>

        {{-- View / Edit / Delete --}}
        @can("{$type}.view")
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])}}"><i class="fa fa-eye" aria-hidden="true"></i> View</a></li>
        @endcan
        @can("{$type}.update")
            <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'edit'], [$id])}}" data-ajax-modal="true" data-title="Edit" class="edit_contact_button"><i class="fa fa-pencil-square-o"></i> Edit</a></li>
        @endcan
        @if(!$is_default)
            @can("{$type}.delete")
                <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'destroy'], [$id])}}" class="delete_contact_button"><i class="fa fa-trash"></i> Delete</a></li>
            @endcan
        @endif

        {{-- More Actions --}}
        @can("{$type}.view")
            <li class="divider"></li>
            <li><a href="javascript:void(0)" data-url="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'balanceDetails'], [$id])}}" data-ajax-modal="true" data-title="Balance Details" class="edit_contact_button"><i class="fa fa-eye"></i> Balance Details</a></li>
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=contact_info"}}"><i class="fa fa-user" aria-hidden="true"></i> Contact Info</a></li>
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=ledger"}}"><i class="fa fa-anchor" aria-hidden="true"></i> Ledger</a></li>
            
            @if(in_array($type, ["both", "supplier"]))
                <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=purchase"}}"><i class="fa fa-arrow-circle-down" aria-hidden="true"></i> Purchases</a></li>
                <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=stock_report"}}"><i class="fa fa-hourglass-half" aria-hidden="true"></i> Stock Report</a></li>
            @endif
            @if(in_array($type, ["both", "customer"]))
                <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=sales"}}"><i class="fa fa-arrow-circle-up" aria-hidden="true"></i> Sales</a></li>
            @endif
            
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=references"}}"><i class="fa fa-link" aria-hidden="true"></i> References</a></li>
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'show'], [$id])."?view=documents_and_notes"}}"><i class="fa fa-paperclip" aria-hidden="true"></i> Documents & Notes</a></li>
            
            <li><a href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'toggleActivate'], [$id])}}">
                    @if($active)
                        <i class="fa fa-times" aria-hidden="true"></i> Deactivate
                    @else
                        <i class="fa fa-check" aria-hidden="true"></i> Activate
                    @endif
                </a></li>
        @endcan
    </ul>
</div>
