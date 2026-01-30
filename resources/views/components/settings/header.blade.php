@props(['icon', 'title', 'description', 'badge' => null])

<div class="d-flex align-items-center gap-3 p-3 section-header-custom" style="background: transparent;">
    @if($icon)
        <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; min-width: 32px; color: #d4af37 !important;">
            <i class="{{ $icon }} fs-5"></i>
        </div>
    @endif
    
    <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-uppercase letter-spacing-1" style="font-size: 0.9rem; letter-spacing: 0.5px; color: #334155;">{{ $title }}</h6>
            @if($badge)
                <span class="badge bg-white text-primary fw-bold" style="font-size: 0.65rem; padding: 0.35em 0.6em; color: #5d4037 !important;">{{ $badge }}</span>
            @endif
        </div>
        @if($description)
            <div class="small mt-1" style="font-size: 0.75rem; color: #64748b !important;">{{ $description }}</div>
        @endif
    </div>
</div>
<div class="p-4 pt-0 border-top-0">
