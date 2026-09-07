<?php

namespace KarimTao\ServerStatus\Http\Controllers;

use Illuminate\Contracts\View\View;

final class ServerStatusController
{
    public function __invoke(): View
    {
        return view('server-status::page');
    }
}
