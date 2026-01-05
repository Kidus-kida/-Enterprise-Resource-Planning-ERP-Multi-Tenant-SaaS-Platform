<div class="row">
    <h4 class="card-title text-primary">Custom Labels Settings</h4>
    <p class="text-muted">Configure custom labels default settings.</p>
    <div class="col-sm-12">
        <strong>labels_for_custom_payments:</strong><br>
    </div>
    @for ($i = 1; $i <= 7; $i++)
        <div class="col-sm-4">
            <div class="form-group mb-3">
                <label class="form-label">@lang('lang_v1.custom_payment_' . $i)</label>
                <input type="text" name="custom_labels[payments][custom_pay_{{ $i }}]" class="form-control"
                    value="{{ $custom_labels['payments']['custom_pay_' . $i] ?? '' }}">
            </div>
        </div>
    @endfor

    <div class="col-sm-12 mt-3">
        <strong>labels_for_custom_fields:</strong><br>
    </div>
    @for ($i = 1; $i <= 4; $i++)
        <div class="col-sm-3">
            <div class="form-group mb-3">
                <label class="form-label">@lang('lang_v1.custom_field_' . $i)</label>
                <input type="text" name="custom_labels[custom_field][custom_field_{{ $i }}]"
                    class="form-control" value="{{ $custom_labels['custom_field']['custom_field_' . $i] ?? '' }}">
            </div>
        </div>
    @endfor
</div>
