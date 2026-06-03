<?php

namespace App\Http\Controllers;

use App\Services\HealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function __construct(
        private HealthService $healthService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $health = $this->healthService->getHealthStatus();
            $status = $health['status'] === 'healthy' ? 200 : 503;

            return response()->json($health, $status);

        } catch (\Exception $e) {
            Log::channel('errors')->error('Health check failed', [
                'action' => 'health_check_failed',
                'business_context' => 'system_monitoring',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error_type' => 'system_error',
            ]);

            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Health check failed',
                'timestamp' => now()->toISOString(),
            ], 503);
        }
    }

    public function detailed(Request $request): JsonResponse
    {
        try {
            return response()->json($this->healthService->getDetailedHealthStatus());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Detailed health check failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
