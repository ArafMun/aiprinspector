<?php

namespace App\Http\Controllers;

use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookService $webhookService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $result = $this->webhookService->handleWebhook($request);

            if (! $result['success']) {
                if ($result['error'] === 'Invalid signature') {
                    Log::warning('Invalid webhook signature received', [
                        'action' => 'webhook_invalid_signature',
                        'business_context' => 'ai_code_review',
                        'ip' => $request->ip(),
                        'event' => $request->header('X-Gitea-Event'),
                        'warning_type' => 'security',
                    ]);
                }

                return response()->json(['error' => $result['error']], $result['status'] ?? 500);
            }

            if (isset($result['ignored']) && $result['ignored']) {
                return response()->json(['ignored' => true]);
            }

            return response()->json(['queued' => true]);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'action' => 'webhook_processing_failed',
                'business_context' => 'ai_code_review',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'error_type' => 'processing_error',
            ]);

            return response()->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
