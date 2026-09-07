<?php

use Illuminate\Support\Facades\Process;
use KarimTao\ServerStatus\MetricUnavailableException;
use KarimTao\ServerStatus\Readers\WindowsReader;

beforeEach(fn () => $this->reader = new WindowsReader)
    ->skip(PHP_OS_FAMILY !== 'Windows', 'runs on Windows only');

test('it reads the average processor load', function () {
    expect($this->reader->cpu())->toBeBetween(0, 100);
});

test('it reads load, cores, memory and uptime', function () {
    $memory = $this->reader->memory();

    expect($this->reader->load())->toHaveCount(1)
        ->and($this->reader->cores())->toBeGreaterThan(0)
        ->and($memory['total'])->toBeGreaterThan($memory['used'])
        ->and($this->reader->uptime())->toBeGreaterThan(0);
});

test('it names the current process and counts its siblings', function () {
    expect($this->reader->process())->toBe('php')
        ->and($this->reader->processes())->toBeGreaterThanOrEqual(1);
});

test('it throws when the cores are not in the environment', function () {
    putenv('NUMBER_OF_PROCESSORS');

    $this->reader->cores();
})->throws(MetricUnavailableException::class, 'NUMBER_OF_PROCESSORS is not set');

test('it throws when powershell fails', function (string $method) {
    Process::fake(fn () => Process::result(output: '', errorOutput: 'boom', exitCode: 1));

    $this->reader->{$method}();
})->with(['cpu', 'load', 'memory', 'uptime', 'process', 'processes'])->throws(MetricUnavailableException::class, 'powershell failed: boom');

test('it throws when the memory sizes cannot be parsed', function () {
    Process::fake(['*TotalVisibleMemorySize*' => Process::result("garbage\r\n")]);

    $this->reader->memory();
})->throws(MetricUnavailableException::class, 'printed no memory sizes');
