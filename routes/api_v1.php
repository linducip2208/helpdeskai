<?php

/*
 * API v1 — versioned re-registration of every endpoint that exists in
 * routes/api.php, using the SAME controllers (no duplication).
 *
 * Final URLs (once loaded): /api/v1/... with route names api.v1.*.
 * Only endpoints present in routes/api.php are listed here.
 */

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:'.config('rate-limits.api_login').',1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('api.auth');
    Route::get('/me', [AuthController::class, 'me'])->name('me')->middleware('api.auth');

    Route::middleware(['api.auth', 'throttle:'.config('rate-limits.api').',1'])->group(function () {
        Route::apiResource('tickets', TicketController::class)->middleware(['idempotency']);

        Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
        Route::get('/knowledge/search', [KnowledgeController::class, 'search'])->name('knowledge.search');
        Route::get('/knowledge/categories', [KnowledgeController::class, 'categories'])->name('knowledge.categories');
        Route::get('/knowledge/category/{category}', [KnowledgeController::class, 'categoryArticles'])->name('knowledge.category');
        Route::get('/knowledge/{article}', [KnowledgeController::class, 'show'])->name('knowledge.show');

        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store')->middleware('idempotency');
        Route::post('/conversations/{conversation}/message', [ConversationController::class, 'message'])->name('conversations.message')->middleware('idempotency');

        Route::post('/ai/classify', [AiController::class, 'classify'])->name('ai.classify')->middleware('throttle:'.config('rate-limits.ai').',1');
        Route::post('/ai/suggest', [AiController::class, 'suggest'])->name('ai.suggest')->middleware('throttle:'.config('rate-limits.ai').',1');
        Route::post('/ai/sentiment', [AiController::class, 'sentiment'])->name('ai.sentiment')->middleware('throttle:'.config('rate-limits.ai').',1');

        Route::get('/analytics/summary', [AnalyticsController::class, 'summary'])->name('analytics.summary');
        Route::get('/analytics/tickets-by-status', [AnalyticsController::class, 'ticketsByStatus'])->name('analytics.tickets-by-status');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    });
});
