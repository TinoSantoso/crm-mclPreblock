<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

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
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $key = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            // Find the user based on the subject (sub) from the token
            $user = User::find($decoded->sub);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Attach the user to the request
            $request->auth = $user; // Lumen's default auth guard might not be fully set up for JWT without custom config
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json(['message' => 'Invalid token signature'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during token decoding', 'error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}