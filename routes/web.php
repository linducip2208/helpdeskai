<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AiFeatureConfigController as AdminAiFeatureController;
use App\Http\Controllers\Admin\AiProviderController;
use App\Http\Controllers\Admin\AiUsageLogController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\AutomationRuleController;
use App\Http\Controllers\Admin\CannedResponseController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConversationController as AdminConversationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\KnowledgeArticleController as AdminKnowledgeController;
use App\Http\Controllers\Admin\KnowledgeCategoryController;
use App\Http\Controllers\Admin\KnowledgeFaqController;
use App\Http\Controllers\Admin\LicenseController as AdminLicenseController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PushSubscriptionController as AdminPushSubscriptionController;
use App\Http\Controllers\Admin\SeoMetaController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SlaPolicyController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WebhookEndpointController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgrammaticSeoController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ServicePageController;
use App\Http\Controllers\User\ConversationController as UserConversationController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TicketController as UserTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/services', [ServicePageController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServicePageController::class, 'show'])->name('services.show');

Route::get('/knowledge-base', [KnowledgeBaseController::class, 'index'])->name('knowledge-base.index');
Route::get('/knowledge-base/search', [KnowledgeBaseController::class, 'search'])->name('knowledge-base.search');
Route::get('/knowledge-base/category/{slug}', [KnowledgeBaseController::class, 'category'])->name('knowledge-base.category');
Route::get('/knowledge-base/{slug}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.show');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.send')->middleware('throttle:10,1');

Route::view('/docs', 'docs')->name('docs');

Route::get('/sitemap.xml', [ProgrammaticSeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [ProgrammaticSeoController::class, 'robots'])->name('robots');

Route::get('/best-helpdesk-software/{year?}', [ProgrammaticSeoController::class, 'bestHelpdesk'])
    ->where('year', '[0-9]{4}')->name('pseo.best');
Route::get('/compare/{slugs}', [ProgrammaticSeoController::class, 'compare'])
    ->where('slugs', '.*')->name('pseo.compare');
Route::get('/alternatives-to/{slug}', [ProgrammaticSeoController::class, 'alternatives'])->name('pseo.alternatives');

Route::middleware(['auth', 'verified', '2fa'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/push/vapid-key', [PushSubscriptionController::class, 'vapidKey'])->name('push.vapid-key');
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/push/test', [PushSubscriptionController::class, 'test'])->name('push.test');
    Route::post('/impersonation/stop', [UserController::class, 'stopImpersonate'])->name('impersonation.stop');
});

Route::middleware(['auth', 'verified', '2fa'])->name('user.')->group(function () {
    Route::resource('tickets', UserTicketController::class)->except(['edit']);
    Route::post('/tickets/{ticket}/reply', [UserTicketController::class, 'reply'])->name('tickets.reply');
    Route::get('/attachments/{attachment}/download', [UserTicketController::class, 'downloadAttachment'])->name('attachments.download');

    Route::get('/conversations', [UserConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [UserConversationController::class, 'show'])->name('conversations.show');
    Route::post('/conversations', [UserConversationController::class, 'store'])->name('conversations.store');
    Route::post('/conversations/{conversation}/message', [UserConversationController::class, 'message'])->name('conversations.message');
    Route::post('/conversations/{conversation}/reply', [UserConversationController::class, 'message'])->name('conversations.reply');
});

Route::middleware(['auth', 'verified', '2fa', 'staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats'])->name('dashboard.stats');

    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index')->middleware('permission:tickets.view');
    Route::get('/tickets/create', [AdminTicketController::class, 'create'])->name('tickets.create')->middleware('permission:tickets.create');
    Route::post('/tickets', [AdminTicketController::class, 'store'])->name('tickets.store')->middleware('permission:tickets.create');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show')->middleware('permission:tickets.view');
    Route::get('/tickets/{ticket}/edit', [AdminTicketController::class, 'edit'])->name('tickets.edit')->middleware('permission:tickets.update');
    Route::match(['put', 'patch'], '/tickets/{ticket}', [AdminTicketController::class, 'update'])->name('tickets.update')->middleware('permission:tickets.update');
    Route::delete('/tickets/{ticket}', [AdminTicketController::class, 'destroy'])->name('tickets.destroy')->middleware('permission:tickets.delete');
    Route::post('/tickets/bulk', [AdminTicketController::class, 'bulkAction'])->name('tickets.bulk')->middleware('permission:tickets.update');
    Route::post('/tickets/{ticket}/star', [AdminTicketController::class, 'star'])->name('tickets.star')->middleware('permission:tickets.update');
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply')->middleware('permission:tickets.reply');
    Route::patch('/tickets/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('tickets.assign')->middleware('permission:tickets.assign');
    Route::patch('/tickets/{ticket}/update-status', [AdminTicketController::class, 'updateStatus'])->name('tickets.update-status')->middleware('permission:tickets.update');
    Route::patch('/tickets/{ticket}/update-priority', [AdminTicketController::class, 'updatePriority'])->name('tickets.update-priority')->middleware('permission:tickets.update');
    Route::post('/tickets/{ticket}/suggest', [AdminTicketController::class, 'suggest'])->name('tickets.suggest')->middleware('permission:ai.use');
    Route::get('/attachments/{attachment}/download', [AdminTicketController::class, 'downloadAttachment'])->name('attachments.download')->middleware('permission:tickets.view');

    Route::resource('departments', DepartmentController::class)->except(['show'])->middleware('permission:departments.manage');
    Route::resource('categories', CategoryController::class)->except(['show'])->middleware('permission:categories.manage');
    Route::resource('users', UserController::class)->middleware('permission:manage_users');
    Route::post('/users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate')->middleware('permission:users.impersonate');

    Route::get('/conversations', [AdminConversationController::class, 'index'])->name('conversations.index')->middleware('permission:tickets.view');
    Route::get('/conversations/create', [AdminConversationController::class, 'create'])->name('conversations.create')->middleware('permission:tickets.view');
    Route::post('/conversations', [AdminConversationController::class, 'store'])->name('conversations.store')->middleware('permission:tickets.view');
    Route::get('/conversations/{conversation}', [AdminConversationController::class, 'show'])->name('conversations.show')->middleware('permission:tickets.view');
    Route::post('/conversations/{conversation}/reply', [AdminConversationController::class, 'reply'])->name('conversations.reply')->middleware('permission:tickets.reply');
    Route::delete('/conversations/{conversation}', [AdminConversationController::class, 'destroy'])->name('conversations.destroy')->middleware('permission:tickets.delete');

    Route::resource('knowledge', AdminKnowledgeController::class)->except(['show'])->middleware('permission:manage_knowledge');
    Route::resource('knowledge-categories', KnowledgeCategoryController::class)->except(['show'])->middleware('permission:manage_knowledge');
    Route::resource('knowledge-faqs', KnowledgeFaqController::class)->except(['show'])->middleware('permission:manage_knowledge');

    Route::resource('canned-responses', CannedResponseController::class)->except(['show'])->middleware('permission:manage_knowledge');
    Route::resource('sla-policies', SlaPolicyController::class)->except(['show'])->middleware('permission:sla.manage');
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index')->middleware('permission:sla.manage');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store')->middleware('permission:sla.manage');
    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy')->middleware('permission:sla.manage');

    Route::get('/webhooks', [WebhookEndpointController::class, 'index'])->name('webhooks.index')->middleware('permission:webhooks.manage');
    Route::post('/webhooks', [WebhookEndpointController::class, 'store'])->name('webhooks.store')->middleware('permission:webhooks.manage');
    Route::get('/webhooks/deliveries', [WebhookEndpointController::class, 'deliveries'])->name('webhooks.deliveries')->middleware('permission:webhooks.manage');
    Route::get('/webhooks/{webhook}/edit', [WebhookEndpointController::class, 'edit'])->name('webhooks.edit')->middleware('permission:webhooks.manage');
    Route::put('/webhooks/{webhook}', [WebhookEndpointController::class, 'update'])->name('webhooks.update')->middleware('permission:webhooks.manage');
    Route::delete('/webhooks/{webhook}', [WebhookEndpointController::class, 'destroy'])->name('webhooks.destroy')->middleware('permission:webhooks.manage');
    Route::post('/webhooks/{webhook}/test', [WebhookEndpointController::class, 'test'])->name('webhooks.test')->middleware('permission:webhooks.manage');
    Route::resource('automation-rules', AutomationRuleController::class)->except(['show'])->middleware('permission:automation.manage');

    Route::resource('ai-providers', AiProviderController::class)->except(['show'])->middleware('permission:ai.configure');
    Route::post('/ai-providers/{provider}/test-connection', [AiProviderController::class, 'testConnection'])->name('ai-providers.test-connection')->middleware('permission:ai.configure');
    Route::post('/ai-providers/{provider}/fetch-models', [AiProviderController::class, 'fetchModels'])->name('ai-providers.fetch-models')->middleware('permission:ai.configure');

    Route::resource('ai-features', AdminAiFeatureController::class)->only(['index', 'edit', 'update'])->middleware('permission:ai.configure');

    Route::resource('posts', PostController::class)->except(['show'])->middleware('permission:manage_knowledge');
    Route::resource('services', ServiceController::class)->except(['show'])->middleware('permission:manage_knowledge');
    Route::resource('email-templates', EmailTemplateController::class)->only(['index', 'edit', 'update'])->middleware('permission:email.manage');
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index')->middleware('permission:audit.view');
    Route::resource('api-keys', ApiKeyController::class)->only(['index', 'store', 'destroy'])->middleware('permission:api.manage');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index')->middleware('permission:reports.view');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'chartData'])->name('analytics.chart-data')->middleware('permission:reports.view');

    Route::get('/export/tickets.csv', [ExportController::class, 'tickets'])->name('export.tickets')->middleware('permission:reports.view');
    Route::get('/export/agents.csv', [ExportController::class, 'agents'])->name('export.agents')->middleware('permission:reports.view');
    Route::get('/export/sla.csv', [ExportController::class, 'sla'])->name('export.sla')->middleware('permission:reports.view');
    Route::get('/export/ai-usage.csv', [ExportController::class, 'aiUsage'])->name('export.ai-usage')->middleware('permission:reports.view');
    Route::get('/export/tickets.xlsx', [ExportController::class, 'ticketsXlsx'])->name('export.tickets.xlsx')->middleware('permission:reports.view');
    Route::get('/export/agents.xlsx', [ExportController::class, 'agentsXlsx'])->name('export.agents.xlsx')->middleware('permission:reports.view');
    Route::get('/export/sla.xlsx', [ExportController::class, 'slaXlsx'])->name('export.sla.xlsx')->middleware('permission:reports.view');
    Route::get('/export/ai-usage.xlsx', [ExportController::class, 'aiUsageXlsx'])->name('export.ai-usage.xlsx')->middleware('permission:reports.view');

    Route::get('/ai-usage-logs', [AiUsageLogController::class, 'index'])->name('ai-usage-logs.index')->middleware('permission:reports.view');

    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/bell', [AdminNotificationController::class, 'bell'])->name('notifications.bell');
    Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/push-subscriptions', [AdminPushSubscriptionController::class, 'index'])->name('push-subscriptions.index')->middleware('permission:settings.manage');
    Route::post('/push-subscriptions/broadcast', [AdminPushSubscriptionController::class, 'broadcast'])->name('push-subscriptions.broadcast')->middleware('permission:settings.manage');
    Route::delete('/push-subscriptions/{pushSubscription}', [AdminPushSubscriptionController::class, 'destroy'])->name('push-subscriptions.destroy')->middleware('permission:settings.manage');

    Route::resource('seo-meta', SeoMetaController::class)->except(['show'])->middleware('permission:manage_knowledge');

    Route::get('/email-logs', [EmailLogController::class, 'index'])->name('email-logs.index')->middleware('permission:email.manage');
    Route::get('/email-logs/{emailLog}', [EmailLogController::class, 'show'])->name('email-logs.show')->middleware('permission:email.manage');
    Route::delete('/email-logs/{emailLog}', [EmailLogController::class, 'destroy'])->name('email-logs.destroy')->middleware('permission:email.manage');

    Route::get('/license', [AdminLicenseController::class, 'index'])->name('license.index')->middleware('permission:settings.manage');
});

require __DIR__.'/auth.php';
require base_path('routes/pair-routes.php');
