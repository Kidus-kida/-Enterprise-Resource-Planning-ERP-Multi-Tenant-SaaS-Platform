@forelse($cheque_lists as $cheque)
    <tr>
        <td>
            <input type="checkbox" name="select_cheques[]" value="{{ $cheque->id }}" class="form-check-input select_cheques">
        </td>
        <td>{{ $cheque->customer_name }}</td>
        <td>{{ $cheque->cheque_number }}</td>
        <td>{{ @format_date($cheque->cheque_date) }}</td>
        <td>{{ $cheque->bank_name }}</td>
        <td>{{ @num_format($cheque->amount) }}</td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">{{ __('No cheques found') }}</td>
    </tr>
@endforelse
