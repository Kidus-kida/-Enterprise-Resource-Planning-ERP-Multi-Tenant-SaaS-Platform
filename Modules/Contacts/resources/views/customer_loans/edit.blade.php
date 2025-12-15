<div class="modal-body">
    <form action="{{ route('customer-loans.update', $transaction->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Customer') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="contact_id" required>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}" {{ $transaction->contact_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Amount') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="number" name="final_total" value="{{ $transaction->final_total }}" step="0.01" required />
                </div>
           </div>
           <div class="col-md-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Date') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="date" name="transaction_date" value="{{ $transaction->transaction_date->format('Y-m-d') }}" required />
                </div>
           </div>
           
           <div class="col-md-12">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Reference No') }}</x-form.label>
                     <x-form.input type="text" name="ref_no" value="{{ $transaction->ref_no }}" />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Note') }}</x-form.label>
                     <textarea class="form-control" name="transaction_note">{{ $transaction->transaction_note }}</textarea>
                </div>
           </div>

            <div class="col-md-12">
                <div class="input-block mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_settlement" value="1" id="is_settlement_edit" {{ $transaction->is_settlement ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_settlement_edit">
                            {{ __('Is Settlement?') }}
                        </label>
                    </div>
                </div>
           </div>
           
        </div>
        <div class="submit-section">
            <x-form.button class="btn btn-primary submit-btn">{{ __('Submit') }}</x-form.button>
        </div>
    </form>
</div>
