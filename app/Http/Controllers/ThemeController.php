<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ThemeController extends Controller
{
    public function toggle(Request $request)
    {
        $currentTheme = $request->cookie('theme', 'light');
        $nextTheme = $currentTheme === 'dark' ? 'light' : 'dark';

        Cookie::queue('theme', $nextTheme, 60 * 24 * 365);

        return redirect()->back(fallback: route('login'));
    }
}
