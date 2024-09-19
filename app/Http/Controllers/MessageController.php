<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Messages;
use App\Models\FriendLists;
use Illuminate\Http\Request;
use App\Notifications\SendMessage;
use Illuminate\Support\Facades\Notification;

class MessageController extends Controller
{
    //get messages
    public function getMessages($friend_id){
        $user = auth()->user()->id;

        $messages = Messages::where(function($query) use ($friend_id, $user) {
            $query->where('from_user_id', $user)
                ->where('to_user_id', $friend_id);
        })->orWhere(function($query) use ($friend_id, $user) {
            $query->where('from_user_id', $friend_id)
                ->where('to_user_id', $user);
        })->orderBy('id', 'desc')
        ->paginate(20);

        return response()->json($messages, 200);
    }

    //send message
    public function sendMessage($friend_id, Request $request){
        $user = auth()->user();
        $message = Messages::create([
            'from_user_id' => $user->id,
            'to_user_id' => $friend_id,
            'message' => $request->message,
            'friend_list_id' => $request->friend_list_id
        ]);

        $friendList = FriendLists::where("id", $request->friend_list_id)->first();

        $friend = User::find($friend_id);
        Notification::send($friend, new SendMessage($friendList, $message, $user));

        return response()->json([
            'status' => true,
            'message' => $message,
            'friend_list' => $friendList
        ], 200,);
    }

    //read message
    public function readMessage($id){
        $user = User::find(auth()->user()->id);
        $user->notifications()
            ->where('type', 'Message')
            ->whereJsonContains('data->from_user_id', intval($id))
            ->delete();
    }
}
