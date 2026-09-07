<?php

namespace KarimTao\ServerStatus;

final class ServerMetrics
{
    public function __construct(
        private Reader $reader,
        private string $disk,
    ) {}

    public function read(): Snapshot
    {
        $cores = $this->reader->cores();
        $memory = $this->reader->memory();
        [$diskTotal, $diskFree] = [disk_total_space($this->disk), disk_free_space($this->disk)];

        if ($diskTotal === false || $diskFree === false) {
            throw MetricUnavailableException::for('disk', $this->disk . ' is not a readable filesystem');
        }

        return new Snapshot(
            cpu: $this->reader->cpu(),
            load: array_map(fn (float $load): int => (int) round($load / $cores * 100), $this->reader->load()),
            cores: $cores,
            memoryTotal: $memory['total'],
            memoryUsed: $memory['used'],
            diskTotal: (int) $diskTotal,
            diskUsed: (int) $diskTotal - (int) $diskFree,
            uptime: $this->reader->uptime(),
            process: $this->reader->process(),
            processes: $this->reader->processes(),
        );
    }
}
