<?php

namespace App\Http\Controllers;

use App\Models\SaveMessage;
use Illuminate\Http\Request;

class SaveMessageController extends Controller
{
    public function create(Request $request){

    }

    public function saveMessages(){
        $userId = auth()->user()->id;

        $data = SaveMessage::where("user_id", $userId)->paginate(20);

        return response()->json($data, 200);
    }
}
