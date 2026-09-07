<?php

namespace KarimTao\ServerStatus\Livewire;

use Illuminate\Contracts\View\View;
use KarimTao\ServerStatus\ServerMetrics;
use Livewire\Component;

final class ServerStatus extends Component
{
    public int $cpu = 0;
    public array $load = [];
    public int $cores = 1;
    public int $memory_total = 0;
    public int $memory_used = 0;
    public int $disk_total = 0;
    public int $disk_used = 0;
    public int $uptime = 0;
    public string $process = '';
    public int $processes = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $snapshot = app(ServerMetrics::class)->read();

        $this->cpu = $snapshot->cpu;
        $this->load = $snapshot->load;
        $this->cores = $snapshot->cores;
        $this->memory_total = $snapshot->memoryTotal;
        $this->memory_used = $snapshot->memoryUsed;
        $this->disk_total = $snapshot->diskTotal;
        $this->disk_used = $snapshot->diskUsed;
        $this->uptime = $snapshot->uptime;
        $this->process = $snapshot->process;
        $this->processes = $snapshot->processes;
    }

    public function render(): View
    {
        return view('server-status::livewire.server-status');
    }
}
