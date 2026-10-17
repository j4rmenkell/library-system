<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    protected $table = "borrowings";
    protected $fillable = ["book_id", "member_id", "borrow_date", "due_date", "return_date", "status"];
}
