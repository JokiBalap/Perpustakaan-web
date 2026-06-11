<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BackupController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Views
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/logout', [AuthController::class, 'logoutGet']);

// Main App Dashboard View
Route::get('/', function () {
    return view('app');
})->middleware('auth');

// Circulation & Database API Routes
Route::group(['prefix' => 'api', 'middleware' => 'auth'], function () {
    Route::get('/state', [LibraryController::class, 'getState']);
    Route::post('/books', [LibraryController::class, 'storeBook']);
    Route::post('/books/{id}/update', [LibraryController::class, 'updateBook']);
    Route::post('/books/{id}/restock', [LibraryController::class, 'restockBook']);
    Route::delete('/books/{id}', [LibraryController::class, 'destroyBook']);
    
    Route::post('/loans', [LibraryController::class, 'borrowBook']);
    Route::post('/loans/return', [LibraryController::class, 'returnBook']);
    
    // Admin specific circulation controls
    Route::post('/admin/loans', [LibraryController::class, 'adminCreateLoan']);
    Route::post('/admin/loans/{id}/update', [LibraryController::class, 'adminUpdateLoan']);
    Route::post('/admin/loans/{id}/return', [LibraryController::class, 'adminReturnLoan']);
    Route::delete('/admin/loans/{id}', [LibraryController::class, 'adminDestroyLoan']);
    
    // Admin specific student management
    Route::post('/admin/students', [LibraryController::class, 'adminCreateStudent']);
    Route::post('/admin/students/{id}/update', [LibraryController::class, 'adminUpdateStudent']);
    Route::delete('/admin/students/{id}', [LibraryController::class, 'adminDestroyStudent']);
    
    // Admin specific backups
    Route::get('/admin/backup-status', [BackupController::class, 'getBackupStatus']);
    Route::get('/admin/backup-download', [BackupController::class, 'downloadLocalBackup']);
    Route::post('/admin/backup-gdrive', [BackupController::class, 'backupToGoogleDrive']);
    
    Route::post('/reservations', [LibraryController::class, 'joinReservation']);
    Route::post('/reservations/cancel', [LibraryController::class, 'cancelReservation']);
    
    Route::post('/notifications/{id}/read', [LibraryController::class, 'markNotificationRead']);
    Route::post('/simulation/time', [LibraryController::class, 'simulateTime']);
    Route::post('/wishlist/{id}/toggle', [LibraryController::class, 'toggleWishlist']);
});
