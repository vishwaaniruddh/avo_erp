<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    public function index(Request $request): void
    {
        $metrics = DashboardService::getMetrics();
        $this->ok($metrics, 'Dashboard metrics retrieved');
    }
}

