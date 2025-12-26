<div class="card mb-3 {{$class ?? ''}}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ $title ?? '' }}</h5>
        @if(isset($tool))
            <div class="card-tools">
                {{ $tool }}
            </div>
        @endif
    </div>
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
