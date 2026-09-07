<?php

use KarimTao\ServerStatus\Reader;
use KarimTao\ServerStatus\ServerMetrics;
use KarimTao\ServerStatus\Snapshot;

function fakeReader(): Reader
{
    return new class extends Reader
    {
        public function cpu(): int
        {
            return 33;
        }

        public function load(): array
        {
            return [1.5, 1.0, 0.5];
        }

        public function cores(): int
        {
            return 4;
        }

        public function memory(): array
        {
            return ['total' => 1000, 'used' => 250];
        }

        public function uptime(): int
        {
            return 90061;
        }

        public function process(): string
        {
            return 'php-fpm';
        }

        public function processes(): int
        {
            return 3;
        }

        protected function read(string $source, string $metric): string
        {
            return $source;
        }
    };
}

test('it reads cpu, load as percent of the cores, memory, disk, uptime and processes', function () {
    $snapshot = (new ServerMetrics(fakeReader(), sys_get_temp_dir()))->read();

    expect($snapshot)->toBeInstanceOf(Snapshot::class)
        ->and($snapshot->cpu)->toBe(33)
        ->and($snapshot->load)->toBe([38, 25, 13])
        ->and($snapshot->cores)->toBe(4)
        ->and($snapshot->memoryTotal)->toBe(1000)
        ->and($snapshot->memoryUsed)->toBe(250)
        ->and($snapshot->diskTotal)->toBeGreaterThan(0)
        ->and($snapshot->diskUsed)->toBeLessThanOrEqual($snapshot->diskTotal)
        ->and($snapshot->uptime)->toBe(90061)
        ->and($snapshot->process)->toBe('php-fpm')
        ->and($snapshot->processes)->toBe(3);
});

test('it throws when the disk path cannot be measured', function () {
    (new ServerMetrics(fakeReader(), '/nonexistent'))->read();
})->throws(Exception::class);
