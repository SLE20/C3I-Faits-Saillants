<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

class AuthenticateBackpackSession extends AuthenticateSession
{
    protected function redirectTo(Request $request): ?string
    {
        return backpack_url('login');
    }
}