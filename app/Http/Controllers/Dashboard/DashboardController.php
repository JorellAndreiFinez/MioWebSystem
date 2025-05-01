<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function showDashboard($uid)
    {
        // Retrieve additional data for the dashboard if necessary
        // You can fetch user data or any other data specific to the dashboard

        return view('mio.head.admin-panel', [
            'page' => 'dashboard',
            'uid' => $uid
        ]);
    }
}
