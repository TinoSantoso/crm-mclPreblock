<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            Log::info('Auth guard api: Processing request for path: ' . $request->path());
            
            $token = $request->bearerToken();
            if ($token) {
                Log::info('Auth guard api: Bearer token found, length: ' . strlen($token));
                
                try {
                    $key = env('JWT_SECRET');
                    if (empty($key)) {
                        Log::error('Auth guard api: JWT_SECRET is not set in environment');
                        return null;
                    }
                    
                    $credentials = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
                    Log::info('Auth guard api: JWT token successfully decoded, sub: ' . $credentials->sub);
                    
                    $user = User::find($credentials->sub);
                    if ($user) {
                        Log::info('Auth guard api: User found: ' . $user->email);
                        return $user;
                    } else {
                        Log::warning('Auth guard api: User not found for sub: ' . $credentials->sub);
                        return null;
                    }
                } catch (\Firebase\JWT\ExpiredException $e) {
                    Log::error('Auth guard api: JWT token expired: ' . $e->getMessage());
                    return null;
                } catch (\Firebase\JWT\SignatureInvalidException $e) {
                    Log::error('Auth guard api: JWT signature invalid: ' . $e->getMessage());
                    return null;
                } catch (\Exception $e) {
                    Log::error('Auth guard api: JWT decoding failed: ' . $e->getMessage());
                    Log::error('Auth guard api: Exception class: ' . get_class($e));
                    return null;
                }
            } else {
                Log::warning('Auth guard api: No bearer token found in request');
            }
            return null;
        });
    }
}
