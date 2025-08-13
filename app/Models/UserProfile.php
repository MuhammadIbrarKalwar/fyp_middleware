<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    //protected $connection = 'mongodb';
	protected $collection = 'user_profiles';

    protected $fillable = [
        'user_id','contact_number', 'address','dob','name'
    ];


    public function skills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function interests()
    {
        return $this->hasMany(UserInterest::class);
    }

}

