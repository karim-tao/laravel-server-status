<?php

use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Readers\DarwinReader;

beforeEach(fn () => $this->reader = new DarwinReader)
    ->skip(PHP_OS_FAMILY !== 'Darwin', 'runs on macOS only');

test('it reads the instantaneous cpu from top', function () {
    expect($this->reader->cpu())->toBeBetween(0, 100);
});

test('it reads load, cores, memory and uptime', function () {
    $memory = $this->reader->memory();

    expect($this->reader->load())->toHaveCount(3)
        ->and($this->reader->cores())->toBeGreaterThan(0)
        ->and($memory['total'])->toBeGreaterThan($memory['used'])
        ->and($this->reader->uptime())->toBeGreaterThan(0);
});

test('it names the current process and counts its siblings', function () {
    expect($this->reader->process())->toBe('php')
        ->and($this->reader->processes())->toBeGreaterThanOrEqual(1);
});

test('it throws when a command fails', function (string $method) {
    Process::fake(fn () => Process::result(output: '', errorOutput: 'boom', exitCode: 1));

    $this->reader->{$method}();
})->with(['cpu', 'cores', 'memory', 'uptime', 'process', 'processes'])->throws(MetricUnavailableException::class, 'failed: boom');

test('it throws when top prints no idle percentage', function () {
    Process::fake(['*top*' => Process::result('Processes: 500 total')]);

    $this->reader->cpu();
})->throws(MetricUnavailableException::class, 'top printed no idle percentage');

test('it throws when vm_stat prints no page size', function () {
    Process::fake([
        '*hw.memsize' => Process::result("17179869184\n"),
        '*vm_stat' => Process::result("Pages active: 1.\n"),
    ]);

    $this->reader->memory();
})->throws(MetricUnavailableException::class, 'vm_stat printed no page size');

test('it throws when kern.boottime has no sec field', function () {
    Process::fake(['*kern.boottime' => Process::result("garbage\n")]);

    $this->reader->uptime();
})->throws(MetricUnavailableException::class, 'kern.boottime has no sec field');
