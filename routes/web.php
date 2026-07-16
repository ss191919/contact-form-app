<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ContactController::class, 'index']);

Route::post('/contacts/confirm', [ContactController::class, 'confirm']);

Route::post('/contacts', [ContactController::class, 'store']);

Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::middleware('auth')->group(function () {

    // 管理画面
    Route::get('/admin', [AdminController::class, 'index']);

    // お問い合わせ
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show']);
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy']);

    // タグ
    Route::post('/admin/tags', [AdminController::class, 'storeTag']);
    Route::get('/admin/tags/{tag}/edit', [AdminController::class, 'editTag']);
    Route::put('/admin/tags/{tag}', [AdminController::class, 'updateTag']);
    Route::delete('/admin/tags/{tag}', [AdminController::class, 'destroyTag']);
});