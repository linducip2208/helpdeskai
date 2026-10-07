<?php

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/api_v1.php';

Route::post('/login', [AuthController::class, 'login'])->name('api.login')->middleware('throttle:'.config('rate-limits.api_login').',1');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout')->middleware('api.auth');
Route::get('/me', [AuthController::class, 'me'])->name('api.me')->middleware('api.auth');

Route::middleware(['api.auth', 'throttle:'.config('rate-limits.api').',1'])->group(function () {
    Route::apiResource('tickets', TicketController::class)->middleware(['idempotency']);

    Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('api.knowledge.index');
    Route::get('/knowledge/search', [KnowledgeController::class, 'search'])->name('api.knowledge.search');
    Route::get('/knowledge/categories', [KnowledgeController::class, 'categories'])->name('api.knowledge.categories');
    Route::get('/knowledge/category/{category}', [KnowledgeController::class, 'categoryArticles'])->name('api.knowledge.category');
    Route::get('/knowledge/{article}', [KnowledgeController::class, 'show'])->name('api.knowledge.show');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('api.conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('api.conversations.show');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('api.conversations.store')->middleware('idempotency');
    Route::post('/conversations/{conversation}/message', [ConversationController::class, 'message'])->name('api.conversations.message')->middleware('idempotency');

    Route::post('/ai/classify', [AiController::class, 'classify'])->name('api.ai.classify')->middleware('throttle:'.config('rate-limits.ai').',1');
    Route::post('/ai/suggest', [AiController::class, 'suggest'])->name('api.ai.suggest')->middleware('throttle:'.config('rate-limits.ai').',1');
    Route::post('/ai/sentiment', [AiController::class, 'sentiment'])->name('api.ai.sentiment')->middleware('throttle:'.config('rate-limits.ai').',1');

    Route::get('/analytics/summary', [AnalyticsController::class, 'summary'])->name('api.analytics.summary');
    Route::get('/analytics/tickets-by-status', [AnalyticsController::class, 'ticketsByStatus'])->name('api.analytics.tickets-by-status');

    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('api.users.show');
});
