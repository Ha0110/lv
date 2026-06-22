<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AdminAttributeController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminManufacturerController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\AuthController;

// API công khai cho trang cửa hàng.
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory']);
Route::get('/search', [ProductController::class, 'search']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API đăng ký, xác thực OTP và đăng nhập.
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);

// API quản trị người dùng.
Route::get('/admin/users', [AdminUserController::class, 'index']);
Route::patch('/admin/users/{sdt}/role', [AdminUserController::class, 'updateRole']);

// API quản trị sản phẩm, biến thể và ảnh.
Route::get('/admin/products', [AdminProductController::class, 'index']);
Route::get('/admin/products/meta', [AdminProductController::class, 'meta']);
Route::post('/admin/products/upload-image', [AdminProductController::class, 'uploadImage']);
Route::post('/admin/products', [AdminProductController::class, 'store']);
Route::put('/admin/products/{id}', [AdminProductController::class, 'update']);
Route::delete('/admin/products/{id}', [AdminProductController::class, 'destroy']);

// API quản trị danh mục, hãng sản xuất và thuộc tính.
Route::get('/admin/catalog/categories', [AdminCategoryController::class, 'index']);
Route::post('/admin/catalog/categories', [AdminCategoryController::class, 'store']);
Route::put('/admin/catalog/categories/{id}', [AdminCategoryController::class, 'update']);
Route::delete('/admin/catalog/categories/{id}', [AdminCategoryController::class, 'destroy']);

Route::get('/admin/catalog/manufacturers', [AdminManufacturerController::class, 'index']);
Route::post('/admin/catalog/manufacturers', [AdminManufacturerController::class, 'store']);
Route::put('/admin/catalog/manufacturers/{id}', [AdminManufacturerController::class, 'update']);
Route::delete('/admin/catalog/manufacturers/{id}', [AdminManufacturerController::class, 'destroy']);

Route::get('/admin/catalog/attributes', [AdminAttributeController::class, 'index']);
Route::post('/admin/catalog/attributes', [AdminAttributeController::class, 'store']);
Route::put('/admin/catalog/attributes/{id}', [AdminAttributeController::class, 'update']);
Route::delete('/admin/catalog/attributes/{id}', [AdminAttributeController::class, 'destroy']);
