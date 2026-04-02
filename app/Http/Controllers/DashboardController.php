<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'borrowers' => auth()->user()->borrowers()->latest()->limit(20)->get(),
        ]);
    }
}
