<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/profile', [UserController::class, 'profile']);
Route::post('/profile/new/photo', [UserController::class, 'uploadPhotoProfile']);
Route::post('/profile/update/photo', [UserController::class, 'updatePhotoProfile']);
Route::post('/profile/new/desc', [UserController::class, 'uploadDescProfile']);
Route::post('/profile/update/desc', [UserController::class, 'updateDescProfile']);

Route::post('/post/new', [PostController::class, 'store']);
Route::get('/posts', [PostController::class, 'all']);
Route::get('/posts/{id}', [PostController::class, 'get']);
Route::delete('/post/{id}/delete', [PostController::class, 'destroy']);
Route::put('/post/{id}/update', [PostController::class, 'update']);

Route::get('/post/{id}/comments', [CommentController::class, 'index']);
Route::post('/post/{id}/comment/new', [CommentController::class, 'store']);
