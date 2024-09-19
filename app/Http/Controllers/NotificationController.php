<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //get notification route controller
    public function notifications(){
        $data = auth()->user()->notifications;

        return response()->json($data, 200);
    }

    //read the unread notification
    public function readNoti(){
        foreach (auth()->user()->unreadNotifications as $notification) {
            if($notification->type != "Message"){
                $notification->markAsRead();
            }
        }
    }
}
