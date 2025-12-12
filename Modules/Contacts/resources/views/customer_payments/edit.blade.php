<div class="modal-body">
    <form action="{{ route('customer-payments.update', $payment->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Customer') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="payment_for" required>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}" {{ $payment->payment_for == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
           
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Amount') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="number" name="amount" value="{{ $payment->amount }}" step="0.01" required />
                </div>
           </div>
           <div class="col-md-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Paid On') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="date" name="paid_on" value="{{ $payment->paid_on->format('Y-m-d') }}" required />
                </div>
           </div>
           
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Method') }}</x-form.label>
                    <select class="form-control select" name="method">
                        <option value="cash" {{ $payment->method == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ $payment->method == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="cheque" {{ $payment->method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="bank_transfer" {{ $payment->method == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>
           </div>
            <div class="col-md-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Ref No') }}</x-form.label>
                     <x-form.input type="text" name="payment_ref_no" value="{{ $payment->payment_ref_no }}" />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Note') }}</x-form.label>
                    <textarea class="form-control" name="note">{{ $payment->note }}</textarea>
                </div>
           </div>
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
