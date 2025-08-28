<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class StaffDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('StaffDashboard');
    }
}