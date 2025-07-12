<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/show/editor', [ImageController::class, 'showEditor'])->name('editor');
Route::post('/process', [ImageController::class, 'processImage'])->name('process.image');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // User Routes
    Route::get('/me', [ChatController::class, 'getCurrentUser']);
    Route::post('/update-presence', [ChatController::class, 'updatePresence']);
    Route::get('/users', [ChatController::class, 'getUsers']);

    // Conversation Routes
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/conversations/start', [ChatController::class, 'startConversation']);
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);

    // Additional Chat Routes
    Route::post('/conversations/{conversationId}/read', [ChatController::class, 'markAsRead']);
    Route::delete('/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
    Route::get('/conversations/{conversationId}', [ChatController::class, 'getConversation']);
});


Route::get('/{any}', function () {
    return view('welcome'); // or whatever view serves your React app
})->where('any', '.*');
