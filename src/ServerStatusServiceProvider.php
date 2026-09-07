<?php

namespace KarimTao\ServerStatus;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use KarimTao\ServerStatus\Livewire\ServerStatus;
use KarimTao\ServerStatus\Readers\DarwinReader;
use KarimTao\ServerStatus\Readers\LinuxReader;
use KarimTao\ServerStatus\Readers\WindowsReader;
use Livewire\Livewire;

final class ServerStatusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Reader::class, fn (): Reader => match (PHP_OS_FAMILY) {
            'Darwin' => new DarwinReader,
            'Windows' => new WindowsReader,
            default => new LinuxReader,
        });

        $this->app->singleton(ServerMetrics::class, fn (Application $app): ServerMetrics => new ServerMetrics(
            reader: $app->make(Reader::class),
            disk: $app->basePath(),
        ));
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'server-status');

        Livewire::component('server-status', ServerStatus::class);
    }
}
