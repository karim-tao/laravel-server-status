<?php

namespace KarimTao\ServerStatus\Readers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Reader;

final class LinuxReader extends Reader
{
    public function cpu(): int
    {
        $result = Process::run("top -bn1 | grep -E '^(%Cpu|CPU)' | awk '{ print $2 + $4 }'");

        if (! $result->successful() || ! is_numeric(trim($result->output()))) {
            throw MetricUnavailableException::for('cpu', 'top printed no cpu usage: ' . trim($result->errorOutput() ?: $result->output()));
        }

        return (int) round((float) trim($result->output()));
    }

    public function load(): array
    {
        $load = sys_getloadavg();

        if ($load === false) {
            throw MetricUnavailableException::for('load', 'sys_getloadavg() is not available');
        }

        return array_map(fn (float $value): float => round($value, 2), $load);
    }

    public function cores(): int
    {
        $cores = preg_match_all('/^processor\s*:/m', $this->read('cpuinfo', 'cores'));

        if ($cores === 0) {
            throw MetricUnavailableException::for('cores', '/proc/cpuinfo lists no processor');
        }

        return $cores;
    }

    public function memory(): array
    {
        preg_match_all('/^(MemTotal|MemAvailable):\s+(\d+) kB/m', $this->read('meminfo', 'memory'), $matches, PREG_SET_ORDER);

        $values = array_column($matches, 2, 1);

        if (! isset($values['MemTotal'], $values['MemAvailable'])) {
            throw MetricUnavailableException::for('memory', '/proc/meminfo has no MemTotal or MemAvailable');
        }

        $total = (int) $values['MemTotal'] * 1024;

        return ['total' => $total, 'used' => $total - (int) $values['MemAvailable'] * 1024];
    }

    public function uptime(): int
    {
        return (int) explode(' ', $this->read('uptime', 'uptime'))[0];
    }

    public function process(): string
    {
        return trim($this->read('self/comm', 'process'));
    }

    public function processes(): int
    {
        $result = Process::run('ps -Ao comm');

        if (! $result->successful()) {
            throw MetricUnavailableException::for('processes', 'ps -Ao comm failed: ' . trim($result->errorOutput()));
        }

        return count(array_keys(array_map('trim', explode("\n", $result->output())), $this->process(), true));
    }

    protected function read(string $source, string $metric): string
    {
        $path = '/proc/' . $source;

        if (! File::isReadable($path)) {
            throw MetricUnavailableException::for($metric, $path . ' is not readable');
        }

        return File::get($path);
    }
}
