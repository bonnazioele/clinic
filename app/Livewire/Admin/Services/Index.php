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
        if (!$this->serviceToToggle) return;

        try {
            // Refresh the service from the database to get the latest state
            $service = $this->serviceToToggle->refresh();
            $clinicsCount = $service->clinics()->count();
            
            if ($service->is_active && $clinicsCount > 0) {
                session()->flash('error', "Cannot deactivate: Service is currently used by {$clinicsCount} clinic(s).");
                return $this->cancelToggle();
            }

            // Toggle the status
            $service->is_active = !$service->is_active;
            $service->save();

            $this->flashMessage('status', "Service successfully " . ($service->is_active ? 'activated' : 'deactivated'));
            
        } catch (\Exception $e) {
            $this->logError($e, 'ToggleStatus');
            $this->flashMessage('error', 'An error occurred while updating the service.');
        }
        $this->cancelToggle();
    }

    protected function logError(\Throwable $e, string $context = 'Unknown')
    {
        \Log::error("[{$context}] " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }

    protected function flashMessage(string $type, string $message)
    {
        session()->flash($type, $message);
    }
}
