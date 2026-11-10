
<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin login
Route::get('/login', function () {
    return view('admin.login');
})->middleware('guest')->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

// Protected admin routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])
        ->name('admin.users.edit');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])
        ->name('admin.users.destroy');
    Route::post('/admin/users/bulk-delete', [UserController::class, 'bulkDestroy'])
        ->name('admin.users.bulk-delete');
    Route::get('/admin/users/export', [UserController::class, 'export'])
        ->name('admin.users.export');
    Route::patch('/admin/users/{user}', [UserController::class, 'update'])
        ->name('admin.users.update');
    Route::get('/admin/users/create', [UserController::class, 'create'])
        ->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])
        ->name('admin.users.store');
    Route::get('/admin/users', [UserController::class, 'index'])
        ->name('admin.users');
});
