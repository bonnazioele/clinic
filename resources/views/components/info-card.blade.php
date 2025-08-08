{{-- Info Card Component --}}
@props([
    'title' => '',
    'value' => '',
    'icon' => '',
    'subtitle' => '',
    'color' => 'primary'
])

<div class="card border-0 shadow-sm h-100">
    <div class="card-body text-center">
        @if($icon)
            <div class="mb-3">
                <i class="{{ $icon }} display-6 text-{{ $color }}"></i>
            </div>
        @endif
        
        <h3 class="mb-1 text-{{ $color }}">{{ $value }}</h3>
        <h6 class="mb-0 fw-bold">{{ $title }}</h6>
        
        @if($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
    </div>
</div>
