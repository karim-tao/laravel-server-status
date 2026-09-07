<?php

namespace KarimTao\ServerStatus\Readers;

use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Reader;

final class WindowsReader extends Reader
{
    public function cpu(): int
    {
        return (int) round((float) $this->read('(Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average).Average', 'cpu'));
    }

    public function load(): array
    {
        return [(float) $this->read('(Get-CimInstance Win32_PerfFormattedData_PerfOS_System).ProcessorQueueLength', 'load')];
    }

    public function cores(): int
    {
        $cores = getenv('NUMBER_OF_PROCESSORS');

        if ($cores === false || $cores === '') {
            throw MetricUnavailableException::for('cores', 'NUMBER_OF_PROCESSORS is not set');
        }

        return (int) $cores;
    }

    public function memory(): array
    {
        $output = $this->read('$os = Get-CimInstance Win32_OperatingSystem; $os.TotalVisibleMemorySize; $os.FreePhysicalMemory', 'memory');

        if (! preg_match('/^(\d+)\s+(\d+)$/', $output, $match)) {
            throw MetricUnavailableException::for('memory', 'Win32_OperatingSystem printed no memory sizes: ' . $output);
        }

        $total = (int) $match[1] * 1024;

        return ['total' => $total, 'used' => $total - (int) $match[2] * 1024];
    }

    public function uptime(): int
    {
        return (int) $this->read('[int]((Get-Date) - (Get-CimInstance Win32_OperatingSystem).LastBootUpTime).TotalSeconds', 'uptime');
    }

    public function process(): string
    {
        return $this->read('(Get-Process -Id ' . getmypid() . ').ProcessName', 'process');
    }

    public function processes(): int
    {
        return (int) $this->read('(Get-Process -Name (Get-Process -Id ' . getmypid() . ').ProcessName | Measure-Object).Count', 'processes');
    }

    protected function read(string $source, string $metric): string
    {
        $result = Process::run('powershell -NoProfile -NonInteractive -Command "' . str_replace('"', '\\"', $source) . '"');

        if (! $result->successful() || trim($result->output()) === '') {
            throw MetricUnavailableException::for($metric, 'powershell failed: ' . trim($result->errorOutput() ?: $result->output()));
        }

        return trim($result->output());
    }
}
