<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FriendLists;
use Illuminate\Http\Request;
use App\Notifications\Unfriend;
use Illuminate\Support\Facades\DB;
use App\Notifications\DeleteFriReq;
use App\Notifications\FriReqCancel;
use App\Notifications\FriendRequest;
use App\Notifications\FriendAccepted;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;

class FriendListsController extends Controller
{
    //send friend req
    public function sendFriReq($id) {
        $test = FriendLists::where(function($query) use ($id) {
            $query->where('first_user_id', '=', auth()->user()->id)
                  ->where('second_user_id', '=', $id);
        })->orWhere(function($query) use ($id) {
            $query->where('second_user_id', '=', auth()->user()->id)
                  ->where('first_user_id', '=', $id);
        })->first();

        if($test && $test->is_delete && $test->first_user_id == auth()->user()->id){
            return response()->json([
                'status' => false,
                'messageForDev' => 'this person cancel delete friend request',
                'message' => 'Cannot send friend request to this person'
            ], 200);
        }elseif($test && $test->is_delete && $test->second_user_id == auth()->user()->id){
            $test->delete();
        }elseif($test && !$test->is_delete){
            return response()->json([
                'status' => false,
                'message' => 'this person already sent you a friend request'
            ], 200);
        }

        $friendList = FriendLists::create([
            "first_user_id" => auth()->user()->id,
            "second_user_id"=> $id
        ]);

        $user = User::find($id);

        Notification::send($user, new FriendRequest($friendList));

        $data = User::select(
            'users.id as friend_id',
            'users.name',
            'users.email',
            'users.profile_photo',
            'friend_lists.id as friend_list_id',
            'friend_lists.first_user_id',
            'friend_lists.second_user_id',
            'friend_lists.is_approve'
        )
        ->join('friend_lists', function($join) use ($friendList) {
            $join->on('users.id', '=', 'friend_lists.second_user_id')
                 ->where('friend_lists.id', '=', $friendList->id);
        })
        ->first();

        return response()->json([
            "status" => true,
            "message" => "friend request sent successfully!",
            "data" => $data,
        ], 200);
    }

    //accept friend request fun
    public function acceptFriReq(Request $request, $friend_list_id){
        $notiId = null;
        $list = FriendLists::find($friend_list_id);
        if(!$list){
            return response()->json([
                "status" => false,
                "message" => "friend not found"
            ], 200);
        }
        $list->is_approve = true;
        $list->save();

        if($request->query('key')){
            DB::table('notifications')->where('id', $request->query('key'))->delete();
        }else{
            $notification = DatabaseNotification::whereJsonContains('data->friend_list_id', intval($friend_list_id))->first();
            $notiId = $notification->id;
            $notification->delete();
        }

        $to_user = User::find($list->first_user_id);

        Notification::send($to_user, new FriendAccepted($list));

        return response()->json([
            "status" => true,
            "message" => "friend request accepted",
            "notiId" => $notiId
        ], 200);
    }

    //cancel friend request
    public function friendRequestCancel($friend_list_id){
        $friendList = FriendLists::where("id", $friend_list_id)->first();
        $second_user = User::where('id', $friendList->second_user_id)->first();
        $first_user = User::where('id', $friendList->first_user_id)->first();

        if($friendList->is_approve){
            return response()->json([
                "status" => false,
                "message" => "that person is already accepted your friend request"
            ], 200);
        }elseif($friendList->is_delete){
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 200);
        }

        $friendList->delete();

        $notification = DatabaseNotification::whereJsonContains('data->friend_list_id', intval($friend_list_id))->first();
        $notiId = $notification->id;
        $notification->delete();

        $data = [
            "notiId" => $notiId,
            "friendList" => $friendList
        ];

        Notification::send($second_user, new FriReqCancel($data));

        return response()->json([
            'status' => true,
            "message" => "friend request canceled"
        ], 200);
    }

    //get friend list
    public function getFriLists(){
        $user = auth()->user()->id;

        $friendData = FriendLists::join('users', function ($join) use ($user) {
            $join->on(function ($query) use ($user) {
                $query->on('friend_lists.first_user_id', '=', 'users.id')
                      ->where('friend_lists.second_user_id', $user);
            })
            ->orOn(function ($query) use ($user) {
                $query->on('friend_lists.second_user_id', '=', 'users.id')
                      ->where('friend_lists.first_user_id', $user);
            });
        })
        ->leftJoin(DB::raw("(SELECT * FROM messages WHERE id IN (
            SELECT MAX(id) FROM messages
            WHERE (from_user_id = $user AND from_user_delete = false)
            OR (to_user_id = $user AND to_user_delete = false)
            GROUP BY friend_lists_id)
        ) as latest_messages"), 'friend_lists.id', '=', 'latest_messages.friend_lists_id')
        ->select(
            'users.id as friend_id',
            'users.name',
            'users.email',
            'users.profile_photo',
            'friend_lists.id as friend_list_id',
            'friend_lists.first_user_id',
            'friend_lists.second_user_id',
            'friend_lists.is_approve',
            'friend_lists.is_delete',
            'friend_lists.updated_at',
            'latest_messages.id as latest_message_id',
            'latest_messages.from_user_id as latest_message_from_user_id',
            'latest_messages.to_user_id as latest_message_to_user_id',
            'latest_messages.message as latest_message_text',
            DB::raw("DATE_FORMAT(latest_messages.created_at, '%Y-%m-%dT%H:%i:%s.000000Z') as latest_message_created_at")
        )
        ->orderByRaw('COALESCE(latest_message_created_at, friend_lists.updated_at) DESC')
        ->paginate(10);

        return response()->json($friendData, 200);
    }

    //unfriend function
    public function unfriend($friend_list_id){
        $friendList = FriendLists::where('id', '=', $friend_list_id)
            ->where(function($query) {
                $query->where('first_user_id', '=', auth()->user()->id)
                    ->orWhere('second_user_id', '=', auth()->user()->id);
            })
            ->first();

        if(!$friendList){
            return response()->json([
                'status' => false,
                'message' => "user not found"
            ], 200);
        }

        $toUser = $friendList->first_user_id == auth()->user()->id ?
            User::find($friendList->second_user_id) :
            User::find($friendList->first_user_id);

        $friendList->delete();

        $noti = DatabaseNotification::whereJsonContains('data->friend_list_id', intval($friend_list_id))->first();
        $notiId = $noti->id;
        $noti->delete();

        $friendList->setAttribute("notiId", $notiId);
        Notification::send($toUser, new Unfriend($friendList));

        return response()->json([
            'status' => true,
            'message' => 'unfriend successfully',
            'notiId' => $notiId
        ], 200);
    }

    //delete friend request
    public function deleteFriReq($friend_list_id){
        $friendList = FriendLists::find($friend_list_id);

        if(!$friendList && !$friendList->second_user_id == auth()->user()->id){
            return response()->json([
                'status' => false,
                'message' => "Sever Error"
            ], 200);
        }elseif(!$friendList && $friendList->second_user_id == auth()->user()->id){
            return response()->json([
                'status' => false,
                'message' => "User not found"
            ], 200);
        }

        $friendList->is_delete = true;
        $friendList->save();

        $notification = DatabaseNotification::whereJsonContains('data->friend_list_id', intval($friend_list_id))->first();
        $notiId = $notification->id;
        $notification->delete();

        $toUser = User::find($friendList->first_user_id);
        Notification::send($toUser,new DeleteFriReq($friendList));

        return response()->json([
            'status' => true,
            'message' => 'friend request delete successfully',
            'notiId' => $notiId
        ], 200);
    }

    //search friend with their name or email
    public function search(Request $request){
        $user = auth()->user()->id;

        $friendData = FriendLists::join('users', function ($join) use ($user) {
            $join->on(function ($query) use ($user) {
                $query->on('friend_lists.first_user_id', '=', 'users.id')
                      ->where('friend_lists.second_user_id', $user);
            })
            ->orOn(function ($query) use ($user) {
                $query->on('friend_lists.second_user_id', '=', 'users.id')
                      ->where('friend_lists.first_user_id', $user);
            });
        })->select(
            'users.id as friend_id',
            'users.name',
            'users.email',
            'users.profile_photo',
            'friend_lists.id as friend_list_id',
            'friend_lists.first_user_id',
            'friend_lists.second_user_id',
            'friend_lists.is_approve',
            'friend_lists.is_delete'
        )
        ->where(function ($query) use ($request){
            $query->where('users.name' , 'like', '%'.$request->query('key').'%')
                ->orWhere('users.email', '=', $request->query('key'));
        })->where("users.id", "!=", auth()->user()->id)
        ->get();

        return response()->json($friendData, 200);
    }
}
