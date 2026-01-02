<?php

use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/pay', [PaymentController::class, 'showForm']);
Route::post('/pay', [PaymentController::class, 'submit']);

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\LogsController;

Route::get('/admin/login', [AuthController::class, 'showLogin']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return redirect('/admin/payments');
    });
    
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::get('/payments/{id}', [AdminPaymentController::class, 'show']);
    Route::post('/payments/{id}/validate', [AdminPaymentController::class, 'validatePayment']);
    Route::post('/payments/{id}/reject', [AdminPaymentController::class, 'rejectPayment']);
    
    Route::get('/payments/{id}/proof', function ($id) {
        $payment = \App\Models\Payment::findOrFail($id);
        return response()->file(storage_path('app/' . $payment->proof_path));
    })->name('admin.proof');

    Route::get('/logs', [LogsController::class, 'index']);
});


