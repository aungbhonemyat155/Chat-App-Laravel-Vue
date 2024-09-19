<?php

use App\Http\Controllers\FriendListsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaveMessageController;
use App\Http\Controllers\TestingController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect("login");
});

Route::middleware(['auth','verified'])->group(function () {
    Route::get("/dashboard", [TestingController::class, "dashboard"])->name("dashboard");
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    //profile picture update route
    Route::post("/profilePic/update", [TestingController::class, "ppUpdate"])->name("profilePic.update");
    //get messages route
    Route::get("/messages/{friend_id}", [MessageController::class, "getMessages"])->name('messages.get');
    //search users
    Route::get("users/", [TestingController::class, "getUsers"])->name("users.get");
    //route for sending friend request
    Route::get("/friend/request/{id}", [FriendListsController::class, "sendFriReq"])->name("friReq.send");
    //testing route
    Route::get("/testing", [TestingController::class, "testingRoute"]);
    //get notification route
    Route::get("/notifications", [NotificationController::class, "notifications"]);
    //read the unread notification
    Route::get("notification/read", [NotificationController::class, "readNoti"]);
    //friend request accept route
    Route::get("/friend/accept/{friend_list_id}", [FriendListsController::class, "acceptFriReq"]);
    //cancel friend request
    Route::get("/friend/request/cancel/{friend_list_id}", [FriendListsController::class, "friendRequestCancel"]);
    //get friend list
    Route::get("friends/lists", [FriendListsController::class, 'getFriLists']);
    //unfriend route
    Route::get('unfriend/{friend_list_id}', [FriendListsController::class, 'unfriend']);
    //get unread notification
    Route::get('notification/refresh', [TestingController::class, 'notiRefresh']);
    //delete friend request
    Route::get('friend/request/delete/{friend_list_id}', [FriendListsController::class, 'deleteFriReq']);
    //send message
    Route::post('send/message/{friend_id}',[MessageController::class, 'sendMessage']);
    //read message
    Route::get("message/read/{id}", [MessageController::class, 'readMessage']);
    //create save meesage
    Route::post('save-message', [SaveMessageController::class, 'create']);
    //get save messages
    Route::get("save-messages", [SaveMessageController::class, 'saveMessages']);
});

Route::get("/controller/testing/{user}", [TestingController::class, "controllerTesting"]);

require __DIR__.'/auth.php';
