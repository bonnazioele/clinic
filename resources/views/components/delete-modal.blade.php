@props([
    'id',                          // e.g. 'deleteServiceModal1'
    'title' => 'Confirm Deletion',
    'route',                       // Required: form action
    'method' => 'DELETE',          // Defaults to DELETE
    'itemName' => null,            // Optional item name to inject
    'deleteText' => 'Delete',
    'cancelText' => 'Cancel',
    'deleteButtonClass' => 'btn-danger',
    'cancelButtonClass' => 'btn-secondary',
    'livewireCancel' => null,      // Optional Livewire method for cancel
])

@if(isset($livewireCancel))
    {{-- Livewire style modal (inline, no Bootstrap modal classes) --}}
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" wire:click="{{ $livewireCancel }}" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @elseif($itemName)
                    Are you sure you want to delete <strong>{{ $itemName }}</strong>?
                @else
                    Are you sure you want to delete this item?
                @endif
            </div>

            <div class="modal-footer">
                <form method="POST" action="{{ $route }}">
                    @csrf
                    @method($method)
                    <button type="button" class="btn {{ $cancelButtonClass }}" wire:click="{{ $livewireCancel }}">{{ $cancelText }}</button>
                    <button type="submit" class="btn {{ $deleteButtonClass }}">{{ $deleteText }}</button>
                </form>
            </div>
        </div>
    </div>
@else
    {{-- Standard Bootstrap modal --}}
    <div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if($slot->isNotEmpty())
                        {{ $slot }}
                    @elseif($itemName)
                        Are you sure you want to delete <strong>{{ $itemName }}</strong>?
                    @else
                        Are you sure you want to delete this item?
                    @endif
                </div>

                <div class="modal-footer">
                    <form method="POST" action="{{ $route }}">
                        @csrf
                        @method($method)
                        <button type="button" class="btn {{ $cancelButtonClass }}" data-bs-dismiss="modal">{{ $cancelText }}</button>
                        <button type="submit" class="btn {{ $deleteButtonClass }}">{{ $deleteText }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
