<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    //
    protected $fillable = [
        'phone', 'session_info',
    ];
}
