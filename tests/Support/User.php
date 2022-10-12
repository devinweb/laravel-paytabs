<?php

namespace Devinweb\LaravelPaytabs\Tests\Support;

use Illuminate\Foundation\Auth\User as Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
}
