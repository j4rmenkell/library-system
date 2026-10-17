<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = "members";

    protected $fillable = [
        "user_id", 
        "name", 
        "contact_number", 
        "address",
    ];
}
