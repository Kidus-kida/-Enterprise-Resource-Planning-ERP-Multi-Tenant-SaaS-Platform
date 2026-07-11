{{--
    Dynamic Field Renderer — renders the correct input widget based on setting.input_type.
    Usage: @include('superadmin::settings.partials._field', ['setting' => $setting])
--}}
@php
    $fieldName  = str_replace('.', '_', $setting->key);
    $fieldId    = 'field_' . $fieldName;
    $value      = $allSettings[$setting->key] ?? $setting->typed_value;
    $depends    = $setting->depends_on;   // "some.key:expected_value" or null
    $depAttr    = $depends ? 'data-depends="' . e($depends) . '"' : '';
    $isDisabled = !$setting->is_editable ? 'disabled' : '';
@endphp

<div class="setting-field-wrap {{ $depends ? 'has-dependency' : '' }}"
     {!! $depAttr !!}
     style="{{ $depends ? 'display:none;' : '' }}">

    {{-- Label --}}
    <label class="setting-label" for="{{ $fieldId }}">
        {{ $setting->label ?? $setting->key }}
        @if($setting->is_sensitive)
            <i class="fa-solid fa-lock text-warning ms-1" title="Encrypted — stored securely"></i>
        @endif
        @if(!$setting->is_editable)
            <span class="badge bg-secondary ms-1" style="font-size:9px;">READ ONLY</span>
        @endif
    </label>

    {{-- Description --}}
    @if($setting->description)
    <p class="setting-description">{{ $setting->description }}</p>
    @endif

    {{-- Render input based on input_type --}}
    @switch($setting->input_type)

        {{-- TEXT / EMAIL / URL / NUMBER / PASSWORD --}}
        @case('text')
        @case('email')
        @case('url')
        @case('number')
            <input type="{{ $setting->input_type }}"
                   class="form-control"
                   id="{{ $fieldId }}"
                   name="{{ $fieldName }}"
                   value="{{ $setting->is_sensitive ? '' : old($fieldName, $value) }}"
                   placeholder="{{ $setting->is_sensitive ? '••••••••' : '' }}"
                   {!! $isDisabled !!}>
            @break

        @case('password')
            <div class="input-group">
                <input type="password"
                       class="form-control"
                       id="{{ $fieldId }}"
                       name="{{ $fieldName }}"
                       value=""
                       placeholder="Leave blank to keep current value"
                       autocomplete="new-password"
                       {!! $isDisabled !!}>
                <button class="btn btn-outline-secondary toggle-password-btn" type="button"
                        data-target="{{ $fieldId }}">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            @break

        {{-- TEXTAREA --}}
        @case('textarea')
            <textarea class="form-control"
                      id="{{ $fieldId }}"
                      name="{{ $fieldName }}"
                      rows="4"
                      {!! $isDisabled !!}>{{ old($fieldName, $value) }}</textarea>
            @break

        {{-- SELECT --}}
        @case('select')
            <select class="form-select" id="{{ $fieldId }}" name="{{ $fieldName }}" {!! $isDisabled !!}>
                @foreach($setting->options ?? [] as $opt)
                    <option value="{{ $opt['value'] }}"
                        {{ old($fieldName, $value) == $opt['value'] ? 'selected' : '' }}>
                        {{ $opt['label'] }}
                    </option>
                @endforeach
            </select>
            @break

        {{-- SWITCH / TOGGLE --}}
        @case('switch')
            <div class="setting-switch-wrap">
                <label class="setting-switch">
                    <input type="hidden" name="{{ $fieldName }}" value="0">
                    <input type="checkbox"
                           id="{{ $fieldId }}"
                           name="{{ $fieldName }}"
                           value="1"
                           class="setting-switch-input"
                           {{ old($fieldName, $value) == '1' ? 'checked' : '' }}
                           {!! $isDisabled !!}>
                    <span class="setting-switch-slider"></span>
                </label>
                <span class="setting-switch-label">
                    {{ old($fieldName, $value) == '1' ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
            @break

        {{-- COLOR PICKER --}}
        @case('color')
            <div class="setting-color-wrap">
                <input type="color"
                       class="color-swatch"
                       id="{{ $fieldId }}_picker"
                       value="{{ old($fieldName, $value ?: '#4e73df') }}"
                       {!! $isDisabled !!}>
                <input type="text"
                       class="form-control color-hex-input"
                       id="{{ $fieldId }}"
                       name="{{ $fieldName }}"
                       value="{{ old($fieldName, $value ?: '#4e73df') }}"
                       maxlength="7"
                       pattern="#[0-9a-fA-F]{6}"
                       data-color-linkedpicker="{{ $fieldId }}_picker"
                       {!! $isDisabled !!}>
                <div class="color-preview" id="{{ $fieldId }}_preview"
                     style="background:{{ $value ?: '#4e73df' }}"></div>
            </div>
            @break

        {{-- IMAGE UPLOAD --}}
        @case('image')
            <div class="setting-image-upload" data-key="{{ $setting->key }}">
                @if($value)
                <div class="current-image-wrap mb-2">
                    <img src="{{ $value }}" alt="Current" class="current-image-preview">
                    <button type="button" class="btn-remove-image" data-key="{{ $setting->key }}"
                            title="Remove image">&times;</button>
                </div>
                @endif
                <div class="image-drop-zone" data-key="{{ $setting->key }}">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Drag &amp; drop or <span>browse</span></p>
                    <small>JPG, PNG, SVG, ICO — max 2MB</small>
                    <input type="file" class="image-file-input" accept="image/*"
                           data-key="{{ $setting->key }}" style="display:none">
                </div>
                <div class="upload-progress" style="display:none">
                    <div class="progress" style="height:4px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%"></div>
                    </div>
                </div>
            </div>
            @break

        {{-- CODE EDITOR --}}
        @case('code')
            <div class="code-editor-wrap">
                <div class="code-editor-toolbar">
                    <span>{{ str_contains($setting->key,'css') ? 'CSS' : 'JavaScript' }}</span>
                    <button type="button" class="btn-code-expand" title="Expand editor">
                        <i class="fa-solid fa-expand"></i>
                    </button>
                </div>
                <textarea class="form-control code-editor"
                          id="{{ $fieldId }}"
                          name="{{ $fieldName }}"
                          rows="10"
                          spellcheck="false"
                          {!! $isDisabled !!}>{{ old($fieldName, $value) }}</textarea>
            </div>
            @break

        {{-- FILE UPLOAD (general) --}}
        @case('file')
            <input type="file" class="form-control" id="{{ $fieldId }}" name="{{ $fieldName }}" {!! $isDisabled !!}>
            @break

        {{-- DEFAULT FALLBACK --}}
        @default
            <input type="text"
                   class="form-control"
                   id="{{ $fieldId }}"
                   name="{{ $fieldName }}"
                   value="{{ old($fieldName, $value) }}"
                   {!! $isDisabled !!}>
    @endswitch

    {{-- Validation Error --}}
    @error($fieldName)
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
