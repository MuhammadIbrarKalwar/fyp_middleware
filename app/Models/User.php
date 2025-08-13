<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;
    //protected $connection = 'mongodb';
	protected $collection = 'users';

    protected $fillable = [
        'id','name', 'email', 'password'
    ];
}
