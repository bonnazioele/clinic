@props([
    'tableClass' => 'table-hover align-middle', // e.g., 'table-striped table-bordered'
    'fixed' => false,                            // true adds table-layout: fixed
    'caption' => null,                           // optional caption for screen readers
    'responsiveBreakpoint' => 'md',              // activates responsive wrapper at this breakpoint
    'loading' => false,                          // if true, shows loading state
])

@php
    $tableStyle = $fixed ? 'table-fixed' : '';
    $responsiveClass = "table-responsive-" . $responsiveBreakpoint;
@endphp

<div class="{{ $responsiveClass }}">
    <table class="table {{ $tableClass }} {{ $tableStyle }}">
        @if ($caption)
            <caption class="visually-hidden">{{ $caption }}</caption>
        @endif

        <thead>
            {{ $headings ?? '' }}
        </thead>

        <tbody>
            @if ($loading)
                {{-- Loading skeleton row --}}
                <tr>
                    <td colspan="100%">
                        <div class="py-4 px-3">
                            <div class="placeholder-glow">
                                <span class="placeholder col-12 mb-2"></span>
                                <span class="placeholder col-10 mb-2"></span>
                                <span class="placeholder col-8"></span>
                            </div>
                        </div>
                    </td>
                </tr>
            @else
                {{ $rows ?? '' }}
            @endif
        </tbody>

        {{ $slot }}
        {{-- Additional content like pagination, empty state, or summaries handled via default slot --}}
    </table>
</div>
