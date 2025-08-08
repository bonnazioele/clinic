{{-- Empty State Component --}}
@props([
    'icon' => 'bi-info-circle',
    'title' => 'No Items Found',
    'message' => 'No items to display at the moment.',
    'actionText' => null,
    'actionUrl' => null,
    'secondaryActionText' => null,
    'secondaryActionUrl' => null,
    'showSecondaryAction' => false
])

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="{{ $icon }} display-1 text-muted mb-3"></i>
        <h5 class="text-muted">{{ $title }}</h5>
        <p class="text-muted mb-3">{{ $message }}</p>
        
        @if($showSecondaryAction && $secondaryActionText && $secondaryActionUrl)
            <a href="{{ $secondaryActionUrl }}" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-clockwise me-2"></i>
                {{ $secondaryActionText }}
            </a>
        @endif
        
        @if($actionText && $actionUrl)
            <a href="{{ $actionUrl }}" class="btn btn-primary">
                <i class="bi bi-plus me-2"></i>
                {{ $actionText }}
            </a>
        @endif
    </div>
</div>
