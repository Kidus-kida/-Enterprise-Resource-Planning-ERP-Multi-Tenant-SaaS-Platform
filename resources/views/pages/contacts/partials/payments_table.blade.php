<table class="table table-bordered table-striped" id="payment_table">
    <thead>
        <tr>
            <th>@lang('messages.date')</th>
            <th>@lang('purchase.payment_ref_no')</th>
            <th>@lang('sale.amount')</th>
            <th>@lang('lang_v1.payment_method')</th>
            <th>@lang('sale.invoice_no')</th>
            <th>@lang('messages.action')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
            <tr>
                <td>@format_datetime($payment->paid_on)</td>
                <td>{{$payment->payment_ref_no}}</td>
                <td>@num_format($payment->amount)</td>
                <td>{{$payment->method}}</td>
                <td>
                    @if($payment->transaction)
                        {{$payment->transaction->invoice_no}}
                    @endif
                </td>
                <td>
                    <button type="button" class="btn btn-info btn-xs view_payment" data-href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'viewPayment'], [$payment->id])}}"><i class="fa fa-eye" aria-hidden="true"></i> @lang("messages.view")</button>
                    <button type="button" class="btn btn-primary btn-xs edit_payment" data-href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'editPayment'], [$payment->id])}}"><i class="fa fa-pencil-square-o"></i> @lang("messages.edit")</button>
                    <button type="button" class="btn btn-danger btn-xs delete_payment" data-href="{{action([\Modules\Contacts\Http\Controllers\ContactController::class, 'destroyPayment'], [$payment->id])}}"><i class="fa fa-trash"></i> @lang("messages.delete")</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
