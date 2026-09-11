<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function __invoke()
    {
        $status = config('services.gemini.key') ? Cache::get('gemini.status', 'Not verified') : 'Not Configured';

        return view('settings', compact('status'));
    }
}
