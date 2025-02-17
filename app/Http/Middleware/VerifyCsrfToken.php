<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'admin/login',
        'admin/logout',
        'landlord/login',
        'landlord/logout',
        'user/reviews',
        'user/reviews/*',
        'user/bookings',
        'user/bookings/*',
        'user/payments/*'
    ];
} 