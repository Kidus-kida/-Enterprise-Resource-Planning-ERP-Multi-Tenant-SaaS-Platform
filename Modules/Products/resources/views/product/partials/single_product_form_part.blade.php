@php if (!isset($business)) {
    $business = \App\Business::find(session('user.business_id'));
} @endphp
@if(!$business->enable_price_tax)
    @php
        $default = 0;
        $class = 'd-none';
      @endphp
@else
    @php
        $default = null;
        $class = '';
      @endphp
@endif

<div class="row g-3 {{$class}}">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Default Purchase Price</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="single_dpp">Exc. Tax <span class="text-danger">*</span></label>
                    <input type="text" name="single_dpp" id="single_dpp" value="{{ old('single_dpp', $default) }}"
                        class="form-control dpp input_number" placeholder="Exc. Tax" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="single_dpp_inc_tax">Inc. Tax <span
                            class="text-danger">*</span></label>
                    <input type="text" name="single_dpp_inc_tax" id="single_dpp_inc_tax"
                        value="{{ old('single_dpp_inc_tax', $default) }}" class="form-control dpp_inc_tax input_number"
                        placeholder="Inc. Tax" required>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">x Margin (%)</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="profit_percent">Profit Margin (%)</label>
                    <input type="text" name="profit_percent" id="profit_percent"
                        value="{{ old('profit_percent', $profit_percent ?? 0) }}" class="form-control input_number"
                        required>
                    <small class="text-muted">{{ __('Manage your profit margin') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Default Selling Price</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="single_dsp">
                        <span class="dsp_label">Exc. Tax</span> <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="single_dsp" id="single_dsp" value="{{ old('single_dsp', $default) }}"
                        class="form-control dsp input_number" placeholder="Exc. Tax" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="single_dsp_inc_tax">
                        <span class="dsp_inc_label">Inc. Tax</span> <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="single_dsp_inc_tax" id="single_dsp_inc_tax"
                        value="{{ old('single_dsp_inc_tax', $default) }}" class="form-control input_number"
                        placeholder="Inc. Tax" required>
                </div>
            </div>
        </div>
    </div>

    @if(empty($quick_add))
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Product Image</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="variation_images">Product Image</label>
                        <input type="file" name="variation_images[]" id="variation_images"
                            class="form-control variation_images" accept="image/*" multiple>
                        <small class="text-muted">
                            {{ __('Max file size: :sizeMB', ['size' => (config('constants.document_size_limit') / 1000000)]) }}
                            <br>{{ __('Aspect ratio should be 1:1') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>