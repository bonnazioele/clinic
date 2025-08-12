@props([
    'id',                // Required unique modal ID
    'title' => 'Are you sure?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'submitAction' => null,
    'method' => 'POST',
    'wireConfirm' => null,
    'wireParams' => [],
    // New props
    'size' => 'md',
    'confirmButtonClass' => 'btn-danger',
    'cancelButtonClass' => 'btn-secondary',
    'confirmButtonIcon' => null,
    'cancelButtonIcon' => null,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
  <div class="modal-dialog modal-{{ $size }}">
    <div class="modal-content shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{ $slot }}
      </div>

      <div class="modal-footer">
        <button type="button" class="btn {{ $cancelButtonClass }}" data-bs-dismiss="modal">
          @if($cancelButtonIcon)<i class="{{ $cancelButtonIcon }} me-1"></i>@endif
          {{ $cancelText }}
        </button>

        @if($wireConfirm)
            @php
                $formattedParams = collect($wireParams)->map(function ($value) {
                    return is_string($value)
                        ? "'" . addslashes($value) . "'"
                        : (is_bool($value) ? ($value ? 'true' : 'false') : $value);
                })->implode(', ');
            @endphp
            <button
                type="button"
                class="btn {{ $confirmButtonClass }}"
                wire:click="{{ $wireConfirm }}{{ $formattedParams ? "($formattedParams)" : '' }}"
                data-bs-dismiss="modal">
                @if($confirmButtonIcon)<i class="{{ $confirmButtonIcon }} me-1"></i>@endif
                {{ $confirmText }}
            </button>
        @elseif($submitAction)
          <form 
            method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" 
            action="{{ $submitAction }}"
            @if($method !== 'GET') data-turbo="false" @endif
            onsubmit="document.getElementById('{{ $id }}').classList.remove('show')">
            @csrf
            @if(!in_array(strtoupper($method), ['GET', 'POST']))
              @method($method)
            @endif
            <button type="submit" class="btn {{ $confirmButtonClass }}">
              @if($confirmButtonIcon)<i class="{{ $confirmButtonIcon }} me-1"></i>@endif
              {{ $confirmText }}
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>
</div>