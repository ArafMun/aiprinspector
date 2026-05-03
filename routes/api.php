<?php

use App\Http\Controllers\WebhookController;
use App\Http\Controllers\HealthController;

Route::middleware('throttle:60,1')->post('/webhook/gitea', [WebhookController::class, 'handle']);
Route::get('/health', [HealthController::class, 'index']);
