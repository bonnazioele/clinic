@props([
    'title',
    'subtitle' => null,
    'actions' => [],            // Supports either 'route' or 'wireClick' per button
    'align' => 'between',       // flex alignment: start | center | between | end
    'stacked' => false,         // if true: flex-column, else flex-row
])

<div class="d-flex {{ $stacked ? 'flex-column' : 'flex-row' }} justify-content-{{ $align }} align-items-start align-items-md-center mb-4 gap-2">
    {{-- Left: Title & Subtitle --}}
    <div>
        <h3 class="mb-0">{{ $title }}</h3>
        @if ($subtitle)
            <small class="text-muted">{{ $subtitle }}</small>
        @endif
    </div>

    {{-- Right: Buttons --}}
    @if (!empty($actions))
        <div class="d-flex gap-2 flex-wrap">
            @foreach ($actions as $action)
                @if (!empty($action['wireClick']))
                    <button
                        type="button"
                        wire:click="{{ $action['wireClick'] }}"
                        class="btn {{ $action['class'] ?? 'btn-primary' }}"
                    >
                        @if (!empty($action['icon']))
                            <i class="{{ $action['icon'] }} me-1"></i>
                        @endif
                        {{ $action['label'] }}
                    </button>
                @elseif (!empty($action['route']))
                    <a
                        href="{{ $action['route'] }}"
                        class="btn {{ $action['class'] ?? 'btn-primary' }}"
                    >
                        @if (!empty($action['icon']))
                            <i class="{{ $action['icon'] }} me-1"></i>
                        @endif
                        {{ $action['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>
