<?php

namespace App\Livewire;

use App\Models\QueueService;
use App\Settings\DisplaySettings;
use Livewire\Component;

class DisplayQueue extends Component
{
    public $waiting = [];
    public $process = [];
    public $finish = [];

    // public DisplaySettings $settings;

    public array $settings = [];

    public function mount(DisplaySettings $settings)
    {
        $this->settings = $settings->toArray();

        $this->brand_name = $settings->brand_name;
        $this->brand_logo = $settings->brand_logo;
        $this->footer = $settings->footer;
        $this->loadQueue();
    }

    public function loadQueue()
    {
        $this->waiting = QueueService::where('status', 'waiting')
            ->orderBy('created_at')
            ->get();

        $this->process = QueueService::where('status', 'processing')
            ->orderBy('updated_at')
            ->get();

        $this->finish = QueueService::where('status', 'finished')
            ->latest()
            ->limit(10)
            ->get();
    }
    public function render()
    {
        return view('livewire.display-queue');
    }
}
