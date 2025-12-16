<div class="modal-body">
    <form action="{{ route('customer-loans.store') }}" method="post">
        @csrf
        <div class="row">
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Customer') }} <span class="text-danger">*</span></x-form.label>
                    <select class="form-control select" name="contact_id" required>
                        @foreach($customers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
           </div>
           <div class="col-md-6">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Amount') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="number" name="final_total" step="0.01" required />
                </div>
           </div>
           <div class="col-md-6">
                 <div class="input-block mb-3">
                    <x-form.label>{{ __('Date') }} <span class="text-danger">*</span></x-form.label>
                    <x-form.input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Reference No') }}</x-form.label>
                    <x-form.input type="text" name="ref_no" />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Approved By') }}</x-form.label>
                    <x-form.input type="text" name="approved_user" />
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <x-form.label>{{ __('Note') }}</x-form.label>
                    <textarea class="form-control" name="transaction_note"></textarea>
                </div>
           </div>
           
           <div class="col-md-12">
                <div class="input-block mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_settlement" value="1" id="is_settlement">
                        <label class="form-check-label" for="is_settlement">
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
