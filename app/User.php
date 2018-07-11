<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    //
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $hidden = ['password'];

    protected $fillable = [
            'name','email','phone' ,'genre','dob','password'
     ];

}
