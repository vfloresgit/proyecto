<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    //
     protected $table = 'personas';
    protected $primaryKey = 'person_id';
    protected $fillable = [
        'name','genre','dob','edad','phone','email'
    ];

}
