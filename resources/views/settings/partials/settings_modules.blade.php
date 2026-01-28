<div class="row">
    @if (!empty($modules))
        <div class="col-md-12">
            <h4 class="card-title text-primary">Modules Settings</h4>
            <p class="text-muted">Configure modules default settings.</p>
        </div>

        @foreach ($modules as $k => $v)
            @if (!isset($enabled_modules_by_subscription) || (isset($enabled_modules_by_subscription[$k]) && $enabled_modules_by_subscription[$k] == 1))
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <div class="form-check form-switch card-title">
                            <input class="form-check-input" type="checkbox" name="enabled_modules[]"
                                value="{{ $k }}" id="module_{{ $k }}"
                                {{ in_array($k, $enabled_modules) ? 'checked' : '' }}>
                            <label class="form-check-label ps-2" for="module_{{ $k }}">
                                {{ $v['name'] }}
                                @if (!empty($v['tooltip']))
                                    <i class="fa fa-info-circle text-info" data-bs-toggle="tooltip"
                                        title="{{ $v['tooltip'] }}"></i>
                                @endif
                            </label>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
