<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use KarimTao\ServerStatus\Http\Controllers\ServerStatusController;
use KarimTao\ServerStatus\Livewire\ServerStatus;

it('renders the bundled page through the controller', function () {
    Route::get('/server-status', ServerStatusController::class);

    $this->get('/server-status')
        ->assertOk()
        ->assertSee('Server Status')
        ->assertSeeLivewire(ServerStatus::class);
});

it('leaves middleware to the application', function () {
    Route::get('/login', fn (): string => 'login')->name('login');
    Route::get('/admin/health', ServerStatusController::class)->middleware('auth');

    $this->get('/admin/health')->assertRedirect('/login');

    $this->withoutMiddleware(Authenticate::class)->get('/admin/health')->assertOk();
});
