<?php

use App\Http\Controllers\API\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1/unibookstore')->group(function () {
    Route::prefix('buku')->group(function () {
        Route::prefix('kategori')->group(function () {
            Route::get('list_data_kategori', [BukuController::class, 'list_data_kategori']);
            Route::get('list', [BukuController::class, 'list_kategori']);
            Route::post('create', [BukuController::class, 'create_kategori']);
            Route::post('edit', [BukuController::class, 'edit_kategori']);
            Route::post('update', [BukuController::class, 'update_kategori']);
            Route::delete('delete', [BukuController::class, 'delete_kategori']);
        });
        Route::get('list', [BukuController::class, 'list_buku']);
        Route::post('create', [BukuController::class, 'create_buku']);
        Route::post('edit', [BukuController::class, 'edit_buku']);
        Route::post('update', [BukuController::class, 'update_buku']);
        Route::delete('delete', [BukuController::class, 'delete_buku']);
    });

    Route::prefix('penerbit')->group(function () {
        Route::get('list_data_penerbit', [BukuController::class, 'list_data_penerbit']);
        Route::get('list', [BukuController::class, 'list_penerbit']);
        Route::post('create', [BukuController::class, 'create_penerbit']);
        Route::post('edit', [BukuController::class, 'edit_penerbit']);
        Route::post('update', [BukuController::class, 'update_penerbit']);
        Route::delete('delete', [BukuController::class, 'delete_penerbit']);
    });

    Route::prefix('pengadaan')->group(function () {
        Route::get('list', [BukuController::class, 'list_pengadaan']);
        Route::get('report_pengadaan', [BukuController::class, 'report_pengadaan']);
    });

    Route::apiResource('products', ProductController::class);
    Route::get('dashboard/statistics', [DashboardController::class, 'statistics']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
