<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class FriendLists extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [ "first_user_id", "second_user_id", "is_approve", "delete" ];
}
