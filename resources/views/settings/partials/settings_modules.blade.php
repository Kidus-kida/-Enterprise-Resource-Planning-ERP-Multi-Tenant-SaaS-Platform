<div class="row">
    @if (!empty($modules))
        <div class="col-md-12">
            <h4 class="card-title text-primary">Modules Settings</h4>
            <p class="text-muted">Configure modules default settings.</p>
        </div>

        @foreach ($modules as $k => $v)
            @if (isset($enabled_moudle_by_subscription) && $enabled_moudle_by_subscription[$k] == 1)
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <div class="form-check form-switch px-0">
                            <label class="form-check-label ms-5">
                                {{ $v['name'] }}
                                @if (!empty($help_explanations[$k]))
                                    <i class="fa fa-info-circle text-info" data-bs-toggle="tooltip"
                                        title="{{ $help_explanations[$k] }}"></i>
                                @endif
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" name="enabled_modules[]"
                                value="{{ $k }}" {{ in_array($k, $enabled_modules) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
