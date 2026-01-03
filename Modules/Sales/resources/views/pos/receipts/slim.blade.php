<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $receipt_details->invoice_no }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; width: 80mm; margin: 0; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .mb-5 { margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; border-bottom: 1px dashed #eee; }
        .total-row { border-top: 1px solid #000; }
        .mt-5 { margin-top: 5px; }
    </style>
</head>
<body>
    <div class="text-center mb-5">
        <h3 style="margin: 0; font-size: 14px;">{{ $receipt_details->business_name }}</h3>
        @if(!empty($receipt_details->location_name))
            <div>{{ $receipt_details->location_name }}</div>
        @endif
        @if(!empty($receipt_details->address))
            <div style="font-size: 10px;">{!! $receipt_details->address !!}</div>
        @endif
    </div>

    <div class="mb-5" style="border-top: 1px solid #000; padding-top: 5px;">
        <div><strong>Inv:</strong> {{ $receipt_details->invoice_no }}</div>
        <div><strong>Date:</strong> {{ $receipt_details->invoice_date }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left">Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt_details->lines as $line)
                <tr>
                    <td>{{ $line['name'] }}</td>
                    <td class="text-right">{{ $line['quantity'] }}</td>
                    <td class="text-right">{{ $line['line_total'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right mt-5">
        <div>Subtotal: {{ $receipt_details->subtotal }}</div>
        @if(!empty($receipt_details->discount))
            <div>Disc: {{ $receipt_details->discount }}</div>
        @endif
        <div class="fw-bold" style="font-size: 13px;">Total: {{ $receipt_details->total }}</div>
    </div>

    @if(!empty($receipt_details->payments))
        <div class="mt-5" style="font-size: 10px;">
            @foreach($receipt_details->payments as $payment)
                <div>{{ $payment['method'] }}: {{ $payment['amount'] }}</div>
            @endforeach
        </div>
    @endif

    <div class="text-center mt-5" style="font-size: 10px; border-top: 1px dashed #000; padding-top: 5px;">
        {!! $receipt_details->footer_text !!}
        <br>Thank you!
    </div>
</body>
</html>
