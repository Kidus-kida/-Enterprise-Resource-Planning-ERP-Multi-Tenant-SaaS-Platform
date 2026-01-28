<div {{ $attributes->merge(['class' => 'card mb-4 border-0 shadow-sm']) }}>
    <div class="card-body p-0">
        {{ $slot }}
    </div>
</div>
