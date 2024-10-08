<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Messages;
use App\Models\FriendLists;
use App\Notifications\MessageDelete;
use Illuminate\Http\Request;
use App\Notifications\SendMessage;
use Carbon\Carbon;
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
            'friend_lists_id' => $request->friend_list_id
        ]);

        $friendList = FriendLists::find($request->friend_list_id);
        $friendList->touch();

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

    //delete message for you
    public function deleteMessageForYou($userId, $messageId){
        $message = Messages::find($messageId);

        $message->update([
            'from_user_delete' => $message->from_user_id == $userId,
            'to_user_delete' => $message->to_user_id == $userId,
        ]);

        return response()->json([
            "message" => "this is delete message for you",
            "data" => $message
        ], 200);
    }

    //delete message for everyone
    public function deleteMessageForEveryone($messageId){
        $message = Messages::find($messageId);

        $fromUserId = $message->from_user_id;
        $friendListId = $message->friend_lists_id;
        $messageData = $message->toArray();

        $message->delete();

        $user = User::find($fromUserId);
        Notification::send($user, new MessageDelete($messageData));

        return response()->json([
            "message" => "this is delete message for everyone",
            "friend_lists_id" => $friendListId
        ], 200);
    }
}
