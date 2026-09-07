<?php

use KarimTao\ServerStatus\Livewire\ServerStatus;
use KarimTao\ServerStatus\Reader;
use KarimTao\ServerStatus\Readers\DarwinReader;
use KarimTao\ServerStatus\Readers\LinuxReader;
use KarimTao\ServerStatus\Readers\WindowsReader;
use KarimTao\ServerStatus\ServerMetrics;
use Livewire\Livewire;

test('it binds the reader matching the operating system', function () {
    expect(app(Reader::class))->toBeInstanceOf(match (PHP_OS_FAMILY) {
        'Darwin' => DarwinReader::class,
        'Windows' => WindowsReader::class,
        default => LinuxReader::class,
    });
});

test('it measures the application root as a singleton', function () {
    $metrics = app(ServerMetrics::class);

    expect($metrics)->toBe(app(ServerMetrics::class))
        ->and((fn () => $this->disk)->call($metrics))->toBe(base_path());
});

test('it registers the livewire component', function () {
    expect(Livewire::new('server-status'))->toBeInstanceOf(ServerStatus::class);
});

test('it registers no route', function () {
    expect(app('router')->getRoutes()->getByName('server-status'))->toBeNull();
});
