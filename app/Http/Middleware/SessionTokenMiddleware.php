<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SessionTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Enhanced logging for debugging CSRF token issue
        Log::info('SessionTokenMiddleware processing request for path: ' . $request->path());
        Log::info('Request method: ' . $request->method());
        Log::info('Request has CSRF token: ' . ($request->header('X-CSRF-TOKEN') ? 'Yes' : 'No'));
        
        // Log session status
        Log::info('Session started: ' . ($request->hasSession() ? 'Yes' : 'No'));
        if ($request->hasSession()) {
            Log::info('Session ID: ' . $request->session()->getId());
        }
        
        // Check if the request already has an Authorization header
        if (!$request->headers->has('Authorization')) {
            Log::info('No Authorization header found in request');
            
            // Get token from session if it exists
            if ($request->session()->has('jwt_token')) {
                $token = $request->session()->get('jwt_token');
                Log::info('Found token in session, length: ' . strlen($token));
                
                // Add the token to the request headers
                $request->headers->set('Authorization', 'Bearer ' . $token);
                Log::info('Added token from session to Authorization header');
            } else {
                Log::info('No token in session, checking cookie or request input');
                
                // Try to get token from sessionStorage via JavaScript
                $token = $request->cookie('jwt_token');
                if ($token) {
                    Log::info('Found token in cookie, length: ' . strlen($token));
                } else {
                    $token = $request->input('token');
                    if ($token) {
                        Log::info('Found token in request input, length: ' . strlen($token));
                    } else {
                        Log::warning('No token found in cookie or request input');
                    }
                }
                
                if ($token) {
                    $request->headers->set('Authorization', 'Bearer ' . $token);
                    Log::info('Added token from cookie/input to Authorization header');
                    
                    // Also store in session for future requests
                    $request->session()->put('jwt_token', $token);
                    Log::info('Stored token in session for future requests');
                }
            }
        } else {
            Log::info('Request already has Authorization header');
        }

        return $next($request);
    }
}