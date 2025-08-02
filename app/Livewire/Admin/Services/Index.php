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
    public $serviceToToggle = null;

    public function mount()
    {
        \Log::info('Livewire Services Index - mount() called');
    }

    public function render()
    {
        \Log::info('Livewire Services Index - render() called');

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
        $this->serviceToToggle = Service::findOrFail($serviceId);
        $this->confirmingToggle = true;
    }

    public function cancelToggle()
    {
        $this->confirmingToggle = false;
        $this->serviceToToggle = null;
    }

    public function toggleStatus()
    {
        if (!$this->serviceToToggle) {
            return;
        }

        try {
            // Refresh the service from the database to get the latest state
            $service = Service::findOrFail($this->serviceToToggle->id);
            
            // Check if trying to deactivate and service is used by clinics
            $clinicsCount = $service->clinics()->count();
            
            if ($service->is_active && $clinicsCount > 0) {
                session()->flash('error', "Cannot deactivate: Service is currently used by {$clinicsCount} clinic(s).");
                $this->cancelToggle();
                return;
            }

            // Toggle the status
            $service->is_active = !$service->is_active;
            $saved = $service->save();
            
            if (!$saved) {
                throw new \Exception('Failed to save service status');
            }

            $status = $service->is_active ? 'activated' : 'deactivated';
            session()->flash('status', "Service successfully {$status}.");
            
        } catch (\Exception $e) {
            \Log::error('Service toggle status failed: ' . $e->getMessage(), [
                'service_id' => $service->id ?? 'unknown',
                'service_name' => $service->service_name ?? 'unknown',
                'exception' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'An error occurred while updating the service status.');
        }

        $this->cancelToggle();
    }
}
