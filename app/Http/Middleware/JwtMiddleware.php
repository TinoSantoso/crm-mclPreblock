<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('JwtMiddleware: Processing request for path: ' . $request->path());
        
        $token = $request->bearerToken();

        if (!$token) {
            Log::warning('JwtMiddleware: No bearer token provided');
            return response()->json(['message' => 'Token not provided'], 401);
        }
        
        Log::info('JwtMiddleware: Bearer token found, length: ' . strlen($token));

        try {
            $key = env('JWT_SECRET');
            if (empty($key)) {
                Log::error('JwtMiddleware: JWT_SECRET is not set in environment');
                return response()->json(['message' => 'Server configuration error'], 500);
            }
            
            Log::info('JwtMiddleware: Attempting to decode JWT token');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            Log::info('JwtMiddleware: JWT token successfully decoded, sub: ' . $decoded->sub);

            // Find the user based on the subject (sub) from the token
            $user = User::find($decoded->sub);

            if (!$user) {
                Log::warning('JwtMiddleware: User not found for sub: ' . $decoded->sub);
                return response()->json(['message' => 'User not found'], 404);
            }
            
            Log::info('JwtMiddleware: User found: ' . $user->email);

            // Attach the user to the request
            // Using a property that's safe to set on the request
            $request->attributes->set('auth_user', $user);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            
            Log::info('JwtMiddleware: User attached to request');

        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::error('JwtMiddleware: Token expired: ' . $e->getMessage());
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('JwtMiddleware: Invalid token signature: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid token signature'], 401);
        } catch (\Exception $e) {
            Log::error('JwtMiddleware: Error during token decoding: ' . $e->getMessage());
            Log::error('JwtMiddleware: Exception class: ' . get_class($e));
            return response()->json(['message' => 'An error occurred during token decoding', 'error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}