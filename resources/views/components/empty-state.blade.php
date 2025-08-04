@props([
    'icon' => 'fas fa-box-open',       // Default icon
    'title' => 'Nothing here yet',
    'subtitle' => null,
    'buttonLabel' => null,
    'buttonRoute' => null,
    'buttonIcon' => 'fas fa-plus',     // Optional icon
])

<div class="text-center py-5">
    <i class="{{ $icon }} fa-3x text-muted mb-3"></i>

    <h5 class="text-muted">{{ $title }}</h5>

    @if ($subtitle)
        <p class="text-muted">{{ $subtitle }}</p>
    @endif

    @if ($buttonRoute && $buttonLabel)
        <a href="{{ $buttonRoute }}" class="btn btn-primary">
            @if ($buttonIcon)
                <i class="{{ $buttonIcon }} me-1"></i>
            @endif
            {{ $buttonLabel }}
        </a>
    @endif
</div>

