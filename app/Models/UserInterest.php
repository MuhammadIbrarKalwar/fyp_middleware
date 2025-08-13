<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_profile_id','name','user_id'
    ];

    public function userProfile()
    {
        return $this->belongsTo(UserProfile::class);
    }
}
