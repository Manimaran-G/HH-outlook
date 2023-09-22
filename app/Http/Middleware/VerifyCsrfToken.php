<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'add-event', // Replace with the actual route name or URL path
        'update-event',
        'cancel-event',
        'delete-event',
        'get-event'
    ];
}
