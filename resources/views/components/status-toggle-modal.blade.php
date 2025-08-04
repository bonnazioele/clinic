<!-- x-status-toggle-modal -->
@props([
    'id' => 'statusToggleModal',
    'title' => 'Confirm Status Change',
    'toggleStatus' => 'toggleStatus',
    'cancelAction' => 'cancelToggle',
    'itemName',
    'isActive',
    'confirming' => false,
    'closing' => false,
])

@if ($confirming)
<div class="modal d-block {{ $closing ? 'modal-fade-out' : 'modal-fade-in' }}" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog {{ $closing ? 'modal-slide-up' : 'modal-slide-down' }}">
        <div class="modal-content shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" wire:click="{{ $cancelAction }}" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to {{ $isActive ? 'deactivate' : 'activate' }} <strong>"{{ $itemName }}"</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="{{ $cancelAction }}">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn {{ $isActive ? 'btn-warning' : 'btn-success' }}" wire:click="{{ $toggleStatus }}">
                    <i class="{{ $isActive ? 'fas fa-pause' : 'fas fa-play' }} me-1"></i>
                    {{ $isActive ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endif