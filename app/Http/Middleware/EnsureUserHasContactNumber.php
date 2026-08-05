<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasContactNumber
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (blank($request->user()->contact_number)) {
            return redirect()->route('contact.edit');
        }

        return $next($request);
    }
}
