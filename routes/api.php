<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EngineerController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFinanceController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\GateMachineStudyController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\StudyRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register-admin', [AuthController::class, 'registerAdmin']);

// Public product and category routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/groups', [GroupController::class, 'index']);
Route::get('/groups/{id}', [GroupController::class, 'show']);

// Public offers routes
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{id}', [OfferController::class, 'show']);

// Admin offers routes (protected by Vue Router)
Route::post('/offers', [OfferController::class, 'store']);
Route::put('/offers/{id}', [OfferController::class, 'update']);
Route::delete('/offers/{id}', [OfferController::class, 'destroy']);

// Settings routes
Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);
// Branding uploads (logo/signature) — same public pattern as settings update
Route::post('/settings/branding', [SettingsController::class, 'updateBranding']);
Route::post('/admin/settings/branding', [SettingsController::class, 'updateBranding']);

// Public project routes
Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{project}', [ProjectController::class, 'show']);

// Public reviews routes (العميل يشوف ويرسل)
Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store']);

// Public appointment routes (جدول المواعيد المتاحة/المحجوزة)
Route::get('/appointments', [AppointmentController::class, 'index']);
Route::post('/appointments/{slot}/book', [AppointmentController::class, 'book']);

// Public study-request routes (استبيان دراسة المشروع)
Route::post('/study-requests', [StudyRequestController::class, 'store']);
Route::post('/gate-machine-studies', [GateMachineStudyController::class, 'store']);

Route::post('/chat', [ChatController::class, 'message']);

// Admin reviews routes (الداشبورد يدير التقييمات)
Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
Route::post('/admin/reviews', [ReviewController::class, 'adminStore']);
Route::put('/admin/reviews/{review}', [ReviewController::class, 'update']);
Route::post('/admin/reviews/{review}', [ReviewController::class, 'update']);
Route::patch('/admin/reviews/{review}', [ReviewController::class, 'toggleVisibility']);
Route::delete('/admin/reviews/{review}', [ReviewController::class, 'destroy']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Product routes
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/upload', [ProductController::class, 'uploadImage']);

    // Category routes
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // Group routes
    Route::post('/groups', [GroupController::class, 'store']);
    Route::post('/groups/{id}', [GroupController::class, 'update']);
    Route::put('/groups/{id}', [GroupController::class, 'update']);
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);

    // Project routes
    Route::get('/admin/projects', [ProjectController::class, 'adminIndex']);
    Route::get('/admin/projects/{project}', [ProjectController::class, 'adminShow']);
    Route::get('/admin/projects/{project}/link-options', [ProjectController::class, 'linkOptions']);
    Route::put('/admin/projects/{project}/link-invoice', [ProjectController::class, 'linkInvoice']);
    Route::put('/admin/projects/{project}/link-quotation', [ProjectController::class, 'linkQuotation']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::post('/projects/{project}', [ProjectController::class, 'update']); // POST for FormData
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // Admin appointment routes (إدارة جدول المواعيد)
    Route::get('/admin/appointments/contacts', [AppointmentController::class, 'contacts']);
    Route::get('/admin/appointments', [AppointmentController::class, 'adminIndex']);
    Route::post('/admin/appointments', [AppointmentController::class, 'store']);
    Route::patch('/admin/appointments/{slot}', [AppointmentController::class, 'update']);
    Route::delete('/admin/appointments/{slot}', [AppointmentController::class, 'destroy']);

    // Admin study-request routes (إدارة طلبات دراسة المشروع)
    Route::get('/admin/study-requests', [StudyRequestController::class, 'adminIndex']);
    Route::get('/admin/study-requests/{studyRequest}', [StudyRequestController::class, 'show']);
    Route::patch('/admin/study-requests/{studyRequest}', [StudyRequestController::class, 'update']);
    Route::delete('/admin/study-requests/{studyRequest}', [StudyRequestController::class, 'destroy']);

    Route::get('/admin/notifications/summary', [AdminNotificationController::class, 'summary']);

    Route::get('/admin/gate-machine-studies', [GateMachineStudyController::class, 'adminIndex']);
    Route::patch('/admin/gate-machine-studies/{gateMachineStudy}', [GateMachineStudyController::class, 'update']);
    Route::delete('/admin/gate-machine-studies/{gateMachineStudy}', [GateMachineStudyController::class, 'destroy']);

    // Admin customers directory (جدول العملاء)
    Route::get('/admin/customers', [CustomerController::class, 'index']);
    Route::post('/admin/customers', [CustomerController::class, 'store']);
    Route::patch('/admin/customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('/admin/customers/{customer}', [CustomerController::class, 'destroy']);

    // Admin engineers directory (جدول المهندسين)
    Route::get('/admin/engineers', [EngineerController::class, 'index']);
    Route::post('/admin/engineers', [EngineerController::class, 'store']);
    Route::patch('/admin/engineers/{engineer}', [EngineerController::class, 'update']);
    Route::delete('/admin/engineers/{engineer}', [EngineerController::class, 'destroy']);

    // Company branding (logo/signature used on exported PDF reports)
    Route::post('/admin/settings/branding', [SettingsController::class, 'updateBranding']);

    // Admin visit reports (تقارير الزيارات مع صور وتوقيع وتصدير PDF)
    Route::get('/admin/reports', [ReportController::class, 'index']);
    Route::get('/admin/reports/{report}', [ReportController::class, 'show']);
    Route::post('/admin/reports', [ReportController::class, 'store']);
    Route::post('/admin/reports/{report}', [ReportController::class, 'update']); // FormData
    Route::put('/admin/reports/{report}', [ReportController::class, 'update']);
    Route::delete('/admin/reports/{report}', [ReportController::class, 'destroy']);
    Route::get('/admin/reports/{report}/html', [ReportController::class, 'html']);
    Route::get('/admin/reports/{report}/pdf', [ReportController::class, 'pdf']);

    // Quotations / Estimates + convert to single invoice
    Route::get('/admin/quotations', [QuotationController::class, 'index']);
    Route::get('/admin/quotations/{quotation}', [QuotationController::class, 'show']);
    Route::post('/admin/quotations', [QuotationController::class, 'store']);
    Route::put('/admin/quotations/{quotation}', [QuotationController::class, 'update']);
    Route::delete('/admin/quotations/{quotation}', [QuotationController::class, 'destroy']);
    Route::get('/admin/quotations/{quotation}/pdf', [QuotationController::class, 'pdf']);
    Route::post('/admin/quotations/{quotation}/invoices', [QuotationController::class, 'createInvoice']);

    Route::get('/admin/invoices', [InvoiceController::class, 'index']);
    Route::get('/admin/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/admin/invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('/admin/invoices/{invoice}', [InvoiceController::class, 'destroy']);
    Route::get('/admin/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);

    // Project finance: payments, expenses, delivery notes
    Route::get('/admin/payments', [ProjectFinanceController::class, 'indexPayments']);
    Route::post('/admin/payments', [ProjectFinanceController::class, 'storePaymentGlobal']);
    Route::get('/admin/delivery-notes', [ProjectFinanceController::class, 'indexDeliveryNotes']);
    Route::post('/admin/projects/{project}/payments', [ProjectFinanceController::class, 'storePayment']);
    Route::patch('/admin/projects/{project}/payments/{payment}', [ProjectFinanceController::class, 'updatePayment']);
    Route::delete('/admin/projects/{project}/payments/{payment}', [ProjectFinanceController::class, 'destroyPayment']);
    Route::get('/admin/projects/{project}/payments/{payment}/html', [ProjectFinanceController::class, 'htmlPayment']);
    Route::get('/admin/projects/{project}/payments/{payment}/pdf', [ProjectFinanceController::class, 'pdfPayment']);
    Route::get('/admin/projects/{project}/finance', [ProjectFinanceController::class, 'showFinance']);
    Route::post('/admin/projects/{project}/expenses', [ProjectFinanceController::class, 'storeExpense']);
    Route::delete('/admin/projects/{project}/expenses/{expense}', [ProjectFinanceController::class, 'destroyExpense']);
    Route::post('/admin/projects/{project}/profit-shares', [ProjectFinanceController::class, 'storeProfitShare']);
    Route::delete('/admin/projects/{project}/profit-shares/{profitShare}', [ProjectFinanceController::class, 'destroyProfitShare']);
    Route::put('/admin/projects/{project}/capital', [ProjectFinanceController::class, 'updateCapital']);
    Route::post('/admin/projects/{project}/delivery-notes', [ProjectFinanceController::class, 'storeDeliveryNote']);
    Route::get('/admin/projects/{project}/delivery-notes/{deliveryNote}', [ProjectFinanceController::class, 'showDeliveryNote']);
    Route::get('/admin/projects/{project}/delivery-notes/{deliveryNote}/html', [ProjectFinanceController::class, 'htmlDeliveryNote']);
    Route::get('/admin/projects/{project}/delivery-notes/{deliveryNote}/pdf', [ProjectFinanceController::class, 'pdfDeliveryNote']);
    Route::delete('/admin/projects/{project}/delivery-notes/{deliveryNote}', [ProjectFinanceController::class, 'destroyDeliveryNote']);
});

