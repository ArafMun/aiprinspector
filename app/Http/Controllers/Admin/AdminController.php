<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {}

    public function dashboard()
    {
        $data = $this->adminService->getDashboardData();

        return view('admin.dashboard', $data);
    }

    public function statistics()
    {
        $data = $this->adminService->getStatisticsData();

        return view('admin.statistics', $data);
    }

    public function statisticsApi(Request $request)
    {
        $range = $request->get('range', 'week');
        $data = $this->adminService->getStatisticsApiData($range);

        return response()->json($data);
    }
}
