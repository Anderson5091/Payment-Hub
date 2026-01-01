use App\Http\Controllers\PaymentController;

Route::get('/pay', [PaymentController::class, 'showForm']);
Route::post('/pay', [PaymentController::class, 'submit']);

use App\Http\Controllers\AuthController;

Route::get('/admin/login', [AuthController::class, 'showLogin']);
Route::post('/admin/login', [AuthController::class, 'login']);
Route::post('/admin/logout', [AuthController::class, 'logout']);

Route::middleware(['admin'])->group(function () {
    Route::get('/admin/dashboard', ...);
    Route::post('/admin/payments/{id}/validate', ...);
});


Route::get('/admin/users', [AdminUserController::class, 'index']);
Route::post('/admin/users', [AdminUserController::class, 'store']);
