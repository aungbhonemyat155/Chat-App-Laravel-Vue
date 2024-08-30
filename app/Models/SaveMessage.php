<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaveMessage extends Model
{
    use HasFactory;

    protected $fillable = [ "message", "user_id", "created_at", "updated_at" ];
}
