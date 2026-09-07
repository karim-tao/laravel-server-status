<?php

namespace KarimTao\ServerStatus;

final readonly class Snapshot
{
    public function __construct(
        public int $cpu,
        public array $load,
        public int $cores,
        public int $memoryTotal,
        public int $memoryUsed,
        public int $diskTotal,
        public int $diskUsed,
        public int $uptime,
        public string $process,
        public int $processes,
    ) {}
}
