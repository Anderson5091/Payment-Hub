use App\Http\Controllers\PaymentController;

Route::get('/pay', [PaymentController::class, 'showForm']);
Route::post('/pay', [PaymentController::class, 'submit']);

use App\Http\Controllers\AuthController;

Route::get('/admin/login', [AuthController::class, 'showLogin']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);


Route::get('/admin/users', [AdminUserController::class, 'index']);
Route::post('/admin/users', [AdminUserController::class, 'store']);

Route::get('/admin/payments/{id}/proof', function ($id) {
    $payment = \App\Models\Payment::findOrFail($id);
    return response()->file(storage_path('app/' . $payment->proof_path));
})->middleware(['auth','admin'])->name('admin.proof');

use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/payments', [AdminPaymentController::class, 'index']);
    Route::get('/payments/{id}', [AdminPaymentController::class, 'show']);
    Route::post('/payments/{id}/validate', [AdminPaymentController::class, 'validatePayment']);
    Route::post('/payments/{id}/reject', [AdminPaymentController::class, 'rejectPayment']);
    Route::get('/logs', [\App\Http\Controllers\Admin\LogsController::class, 'index']);
});


