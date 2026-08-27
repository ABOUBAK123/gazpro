<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\AppSetting;

class LandingController extends Controller
{
    public function index()
    {
        $plans = Plan::active()->get();
        return view('landing', compact('plans'));
    }

    public function terms()
    {
        $terms = AppSetting::get('terms_of_service', '');
        return view('terms', compact('terms'));
    }
}
