<?php

namespace KarimTao\ServerStatus\Readers;

use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Reader;

final class DarwinReader extends Reader
{
    public function cpu(): int
    {
        $output = $this->read("/usr/bin/top -l 2 -s 1 -n 0 | grep 'CPU usage' | tail -1", 'cpu');

        if (! preg_match('/([\d.]+)% idle/', $output, $match)) {
            throw MetricUnavailableException::for('cpu', 'top printed no idle percentage: ' . $output);
        }

        return (int) round(100 - (float) $match[1]);
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
        return (int) $this->read('/usr/sbin/sysctl -n hw.ncpu', 'cores');
    }

    public function memory(): array
    {
        $total = (int) $this->read('/usr/sbin/sysctl -n hw.memsize', 'memory');
        $stats = $this->read('/usr/bin/vm_stat', 'memory');

        if (! preg_match('/page size of (\d+) bytes/', $stats, $page)) {
            throw MetricUnavailableException::for('memory', 'vm_stat printed no page size');
        }

        preg_match_all('/^Pages (active|wired down|occupied by compressor):\s+(\d+)/m', $stats, $matches, PREG_SET_ORDER);

        if (count($matches) !== 3) {
            throw MetricUnavailableException::for('memory', 'vm_stat printed no active, wired or compressed pages');
        }

        return ['total' => $total, 'used' => array_sum(array_column($matches, 2)) * (int) $page[1]];
    }

    public function uptime(): int
    {
        $output = $this->read('/usr/sbin/sysctl -n kern.boottime', 'uptime');

        if (! preg_match('/sec = (\d+)/', $output, $match)) {
            throw MetricUnavailableException::for('uptime', 'kern.boottime has no sec field: ' . $output);
        }

        return time() - (int) $match[1];
    }

    public function process(): string
    {
        return $this->name($this->read('ps -p ' . getmypid() . ' -o comm=', 'process'));
    }

    public function processes(): int
    {
        $names = array_map(fn (string $line): string => $this->name($line), explode("\n", $this->read('ps -Ao comm', 'processes')));

        return count(array_keys($names, $this->process(), true));
    }

    private function name(string $command): string
    {
        return basename(strtok(trim($command), ': '));
    }

    protected function read(string $source, string $metric): string
    {
        $result = Process::run($source);

        if (! $result->successful() || trim($result->output()) === '') {
            throw MetricUnavailableException::for($metric, $source . ' failed: ' . trim($result->errorOutput() ?: $result->output()));
        }

        return trim($result->output());
    }
}
