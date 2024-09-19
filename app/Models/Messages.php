<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    use HasFactory;

    protected $fillable = [ "from_user_id", "to_user_id", "message", "from_user_delete", "to_user_delete", "friend_lists_id" ];

    public function friendList()
    {
        return $this->belongsTo(FriendLists::class, 'friend_lists_id');
    }
}
