<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout')->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->name('api.me')->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tickets', TicketController::class);

    Route::get('/knowledge', [KnowledgeController::class, 'index'])->name('api.knowledge.index');
    Route::get('/knowledge/{article}', [KnowledgeController::class, 'show'])->name('api.knowledge.show');
    Route::get('/knowledge/category/{category}', [KnowledgeController::class, 'category'])->name('api.knowledge.category');
    Route::get('/knowledge/search', [KnowledgeController::class, 'search'])->name('api.knowledge.search');

    Route::get('/conversations', [ConversationController::class, 'index'])->name('api.conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('api.conversations.show');
    Route::post('/conversations', [ConversationController::class, 'store'])->name('api.conversations.store');
    Route::post('/conversations/{conversation}/message', [ConversationController::class, 'message'])->name('api.conversations.message');

    Route::post('/ai/classify', [AiController::class, 'classify'])->name('api.ai.classify');
    Route::post('/ai/suggest', [AiController::class, 'suggest'])->name('api.ai.suggest');
    Route::post('/ai/sentiment', [AiController::class, 'sentiment'])->name('api.ai.sentiment');

    Route::get('/analytics/summary', [AnalyticsController::class, 'summary'])->name('api.analytics.summary');
    Route::get('/analytics/tickets-by-status', [AnalyticsController::class, 'ticketsByStatus'])->name('api.analytics.tickets-by-status');

    Route::get('/users', [UserController::class, 'index'])->name('api.users.index');
});
