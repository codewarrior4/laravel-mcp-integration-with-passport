<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function show(User $user)
    {
        
        return $user;
    }

    public function transactions(User $user)
    {
        return $user->transactions;
    }
}
