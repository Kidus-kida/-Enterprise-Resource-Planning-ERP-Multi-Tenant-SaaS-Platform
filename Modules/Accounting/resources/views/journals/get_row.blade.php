<tr>
    <td>
        <input type="hidden" class="row_index" value="{{ $index }}">
        {!! Form::select('journal[account_type_id][' . $index . ']', $account_types, null, [
            'class' => 'form-control select2 account_type',
            'placeholder' => __('messages.please_select'),
            'style' => 'width:100%',
        ]) !!}
    </td>
    <td>
        {!! Form::select('journal[account_id][' . $index . ']', $accounts, null, [
            'class' => 'form-control select2',
            'id' => 'account_id_' . $index,
            'placeholder' => __('messages.please_select'),
            'style' => 'width:100%',
        ]) !!}
    </td>
    <td>
        {!! Form::number('journal[debit_amount][' . $index . ']', null, [
            'class' => 'form-control debit_amount',
            'placeholder' => '0.00',
            'step' => '0.01',
        ]) !!}
    </td>
    <td>
        {!! Form::number('journal[credit_amount][' . $index . ']', null, [
            'class' => 'form-control credit_amount',
            'placeholder' => '0.00',
            'step' => '0.01',
        ]) !!}
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-sm remove_row"><i class="fa fa-trash"></i></button>
    </td>
</tr>
