<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Readers\LinuxReader;

beforeEach(fn () => $this->reader = new LinuxReader)
    ->skip(PHP_OS_FAMILY !== 'Linux', 'runs on Linux only');

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
    expect($this->reader->process())->not->toBeEmpty()
        ->and($this->reader->processes())->toBeGreaterThanOrEqual(1);
});

test('it throws when a file is not readable', function (string $method) {
    File::shouldReceive('isReadable')->andReturn(false);

    $this->reader->{$method}();
})->with(['cores', 'memory', 'uptime', 'process'])->throws(MetricUnavailableException::class, '/proc/');

test('it throws when top prints no cpu usage', function () {
    Process::fake(['top *' => Process::result('garbage')]);

    $this->reader->cpu();
})->throws(MetricUnavailableException::class, 'top printed no cpu usage');

test('it throws when /proc/cpuinfo lists no processor', function () {
    File::shouldReceive('isReadable')->andReturn(true);
    File::shouldReceive('get')->andReturn("vendor_id\t: Test\n");

    $this->reader->cores();
})->throws(MetricUnavailableException::class, '/proc/cpuinfo lists no processor');

test('it throws when /proc/meminfo has no totals', function () {
    File::shouldReceive('isReadable')->andReturn(true);
    File::shouldReceive('get')->andReturn("MemFree: 1000 kB\n");

    $this->reader->memory();
})->throws(MetricUnavailableException::class, '/proc/meminfo has no MemTotal or MemAvailable');

test('it throws when ps fails', function () {
    Process::fake(['ps *' => Process::result(output: '', errorOutput: 'not found', exitCode: 1)]);

    $this->reader->processes();
})->throws(MetricUnavailableException::class, 'Unable to read processes: ps -Ao comm failed: not found');
