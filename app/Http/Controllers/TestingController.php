<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Mail\TestingMail;
use App\Models\FriendLists;
use App\Models\Messages;
use App\Models\User;
use App\Notifications\DeleteFriReq;
use App\Notifications\FriendAccepted;
use App\Notifications\FriendRequest;
use App\Notifications\FriReqCancel;
use App\Notifications\SendMessage;
use App\Notifications\Unfriend;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TestingController extends Controller
{
    public function dashboard(Request $request){
        $user = User::find($request->user()->id);
        $response = $this->getFriLists();
        $result = json_decode($response->getContent(), true);
        $notifications = $user->notifications()->where('type', '!=', "Message")->get();
        $messageNotifications = $user->notifications()
            ->where('type', 'Message')
            ->get()
            ->groupBy(function ($item) {
                return $item->data['from_user_id'];
            });

        $data = [
            'userData' => [
                'mustVerifyEmail' => $user instanceof MustVerifyEmail,
                'status' => session('status'),
                'user' => $user->toArray(),
                'unreadNotiCount' => $user->unreadNotifications->count()
            ] ,
            'notifications' => $notifications,
            'friendLists' => $result,
            'messageNotifications' => $messageNotifications
        ];

        return Inertia::render('Dashboard',["data" => $data]);
    }

    //update profile photo code
    public function ppUpdate(Request $request){
        $user = auth()->user();

        if($user->profile_photo){
            Storage::delete('public/'.$user->profile_photo);
        }

        $path = Storage::put("/public",$request->file);

        $path = basename($path);

        User::find($user->id)->update([
            "profile_photo" => $path
        ]);

    }

    //get users data
    public function getUsers(Request $request)
    {
        $user = auth()->user()->id;

        $result = FriendLists::rightJoin('users', function ($join) use ($user) {
            $join->on(function ($query) use ($user) {
                $query->on('friend_lists.first_user_id', '=', 'users.id')
                    ->where('friend_lists.second_user_id', $user);
            })
            ->orOn(function ($query) use ($user) {
                $query->on('friend_lists.second_user_id', '=', 'users.id')
                    ->where('friend_lists.first_user_id', $user);
            });
        })
        ->select(
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
        })->where("users.id", "!=", auth()->user()->id)->get();

        return response()->json($result, 200);
    }

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

    //testing vue route
    public function testingRoute(){
        return Inertia::render("Testing");
    }

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
            'friend_lists.last_message',
            'friend_lists.updated_at'
        )
        ->orderBy('friend_lists.updated_at', 'desc')
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

    //refresh notification
    public function notiRefresh(){
        $noti = auth()->user()->notifications;
        $unreadNotiCount = auth()->user()->unreadNotifications->count();

        $response = $this->getFriLists();
        $friendLists = json_decode($response->getContent(), true);

        return response()->json([
            "noti" => $noti,
            "unreadNotiCount" => $unreadNotiCount,
            "friendLists" => $friendLists
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
            'message' => $request->message
        ]);

        FriendLists::where("id",$request->friend_list_id)->update(['last_message' => $message->toJson()]);
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

    public function controllerTesting(){
        $email = new TestingMail();
        Mail::to('aungbhonemyat648@gmail.com')->send($email);
    }
}
