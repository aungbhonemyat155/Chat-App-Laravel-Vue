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

    //update profile photo
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
            'friend_lists.updated_at'
        )
        ->orderBy('friend_lists.updated_at', 'desc')
        ->paginate(10);

        return response()->json($friendData, 200);
    }

    //testing vue route
    public function testingRoute(){
        return Inertia::render("Testing");
    }

    public function controllerTesting($user){


        // $friendData = FriendLists::join('users', function ($join) use ($user) {
        //     $join->on(function ($query) use ($user) {
        //         $query->on('friend_lists.first_user_id', '=', 'users.id')
        //             ->where('friend_lists.second_user_id', $user);
        //     })
        //     ->orOn(function ($query) use ($user) {
        //         $query->on('friend_lists.second_user_id', '=', 'users.id')
        //             ->where('friend_lists.first_user_id', $user);
        //     });
        // })
        // ->select(
        //     'users.id as friend_id',
        //     'users.name',
        //     'users.email',
        //     'users.profile_photo',
        //     'friend_lists.id as friend_list_id',
        //     'friend_lists.first_user_id',
        //     'friend_lists.second_user_id',
        //     'friend_lists.is_approve',
        //     'friend_lists.is_delete',
        //     'friend_lists.last_message',
        //     'friend_lists.updated_at'
        // )
        // ->orderBy('friend_lists.updated_at', 'desc')
        // ->paginate(10);

        // $friendData = Messages::where(function($query) use ($user){
        //     $query->where('from_user_id', $user)
        //         ->orWhere('to_user_id', $user);
        //     })->join(function($join) use ($user){
        //         $join->on(function($query) use ($user){
        //             $query->on('fri')
        //         })
        //     })

        // return response()->json($friendData, 200);

        $friendListsWithLatestMessages = FriendLists::with('latestMessage')
            ->where('first_user_id', $user)
            ->orWhere('second_user_id', $user)
            ->get();

        return response()->json($friendListsWithLatestMessages, 200);
    }
}
