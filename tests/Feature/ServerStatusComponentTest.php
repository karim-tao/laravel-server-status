<?php

use KarimTao\ServerStatus\Livewire\ServerStatus;
use Livewire\Livewire;

test('it renders the current metrics and refreshes every five seconds', function () {
    Livewire::test(ServerStatus::class)
        ->assertSee('CPU')
        ->assertSee('Memory')
        ->assertSee('Disk')
        ->assertSee('Uptime')
        ->assertSee('processes')
        ->assertSee('Refreshed every 5 seconds')
        ->tap(function ($component) {
            expect($component->get('load'))->not->toBeEmpty()
                ->and($component->get('disk_total'))->toBeGreaterThan(0)
                ->and($component->get('process'))->not->toBeEmpty()
                ->and($component->get('processes'))->toBeGreaterThanOrEqual(1);
        })
        ->call('refresh')
        ->assertOk();
});
