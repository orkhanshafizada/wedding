<?php

namespace App\Http\Controllers\Web;

class AuthController
{

    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }
}
