@props([
    'label', 
    'description' => null, 
    'tooltip' => null, 
    'indent' => false,
    'configureLink' => null,
    'showConfigure' => false
])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center py-3 mb-0 border-bottom last-child-border-0 ' . ($indent ? 'ms-5 ps-3 border-start' : '')]) }}>
    <div class="flex-grow-1 pe-4" style="max-width: 70%;">
        <div class="d-flex align-items-center gap-2">
            <label class="form-label fw-semibold mb-0 text-dark">{{ $label }}</label>
            @if($tooltip)
                <i class="la la-info-circle text-muted" data-bs-toggle="tooltip" title="{{ $tooltip }}" style="cursor: help; font-size: 0.9rem;"></i>
            @endif
        </div>
        
        @if($description)
            <p class="small text-muted mb-0 mt-1" style="line-height: 1.4; font-size: 0.75rem;">{{ $description }}</p>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3 ms-auto flex-shrink-0">
        @if($configureLink)
            <a href="{{ $configureLink }}" 
               id="config_link_{{ $attributes->get('id') ?? str_replace(' ', '_', strtolower($label)) }}"
               class="btn btn-link btn-sm p-0 text-decoration-none fw-medium d-flex align-items-center gap-1 {{ $showConfigure ? '' : 'd-none' }}" 
               style="font-size: 0.75rem;">
                {{ __('Configure') }}
                <i class="la la-angle-right"></i>
            </a>
        @endif

        {{ $slot }}
    </div>
</div>
