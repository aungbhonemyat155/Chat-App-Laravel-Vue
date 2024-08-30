<?php

namespace App\Http\Controllers;

use App\Models\SaveMessage;
use Illuminate\Http\Request;

class SaveMessageController extends Controller
{
    public function create(Request $request){
        $user = auth()->user()->id;

        if(!$user){
            return response()->setStatusCode(403);
        }

        $saveMessage = new SaveMessage();
        $saveMessage->message = request()->message;
        $saveMessage->user_id = $user;
        $saveMessage->save();

        return response()->json($saveMessage, 201);
    }

    public function saveMessages(){
        $userId = auth()->user()->id;

        $data = SaveMessage::where("user_id", $userId)->orderBy('id', "desc")->paginate(20);

        return response()->json($data, 200);
    }
}
