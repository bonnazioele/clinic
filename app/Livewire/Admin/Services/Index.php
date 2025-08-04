<?php

namespace App\Livewire\Admin\Services;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $confirmingToggle = false;
    public $closing = false;
    public $serviceToToggle = null;
    public $serviceToToggleId = null; // Cache service ID
    public $serviceToToggleName = null; // Cache service name
    public $serviceToToggleStatus = null; // Cache current status

    public $message = null;
    public $error = null;

    public $confirmingDelete = false;
    public $serviceToDelete = null;
    public $serviceToDeleteName = null; // Cache the service name

    public function mount()
    {
    }

    public function render()
    {
        $services = collect();

        try {
            $services = Service::with('clinics')->latest()->paginate(10);
            \Log::info('Loaded services: ' . $services->count());
        } catch (\Exception $e) {
            $this->logError($e, 'Render');
        }
        return view('livewire.admin.services.index', compact('services'))->layout('admin.layouts.app');
    }

    public function confirmToggle($serviceId)
    {
        $service = Service::findOrFail($serviceId);
        $this->serviceToToggleId = $service->id;
        $this->serviceToToggleName = $service->service_name;
        $this->serviceToToggleStatus = $service->is_active;
        $this->confirmingToggle = true;
    }

    public function cancelToggle()
    {
        $this->closing = true;
        $this->dispatch('close-modal', ['delay' => 300]);
    }

    public function actuallyCancel()
    {
        $this->confirmingToggle = false;
        $this->closing = false;
        $this->serviceToToggle = null;
        $this->serviceToToggleId = null;
        $this->serviceToToggleName = null;
        $this->serviceToToggleStatus = null;
    }

    public function toggleStatus()
    {
        if (!$this->serviceToToggleId) return;

        try {
            $service = Service::findOrFail($this->serviceToToggleId);
            $clinicsCount = $service->clinics()->count();

            if ($service->is_active && $clinicsCount > 0) {
                $this->error = "Cannot deactivate: Service is currently used by {$clinicsCount} clinic(s).";
                $this->closing = true;
                $this->dispatch('close-modal', ['delay' => 300]);
                return;
            }
            $service->is_active = !$service->is_active;
            $service->save();

            $this->message = "Service successfully " . ($service->is_active ? 'activated' : 'deactivated');

        } catch (\Exception $e) {
            $this->logError($e, 'ToggleStatus');
            $this->error = 'An error occurred while updating the service.';
        }
        
        $this->closing = true;
        $this->dispatch('close-modal', ['delay' => 300]);
    }

    protected function logError(\Throwable $e, string $context = 'Unknown')
    {
        \Log::error("[{$context}] " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }

    public function confirmDelete($id)
    {
        $service = Service::findOrFail($id);
        $this->serviceToDelete = $service->id; // Store only the ID
        $this->serviceToDeleteName = $service->service_name; // Cache the name
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->reset(['confirmingDelete', 'serviceToDelete', 'serviceToDeleteName']);
    }

    public function closeWithAnimation()
    {
        $this->closing = true;
        $this->dispatch('close-modal-after-delay');
    }
}
