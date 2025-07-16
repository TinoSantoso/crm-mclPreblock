<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // Generate JWT after successful registration
            $token = $this->generateJwtToken($user);

            return response()->json(['message' => 'User registered successfully', 'token' => $token], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User registration failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Authenticate a user and return a JWT.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $this->generateJwtToken($user);
        
        return response()->json(['token' => $token, 'message' => 'Login successful'], 200);
    }

    /**
     * Log the user out (invalidate token - conceptually).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // For JWT, logout is typically handled client-side by deleting the token.
        // On the server, you might blacklist tokens if you have a robust system,
        // but for stateless JWT, simply not sending the token is enough.
        // Clear the session token if it exists
        if ($request->session()->has('jwt_token')) {
            $request->session()->forget('jwt_token');
        }
        
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
    
    /**
     * Store JWT token in session for server-side access.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeToken(Request $request)
    {
        Log::info('storeToken method called');
        
        try {
            // Log the request details
            Log::info('Request method: ' . $request->method());
            
            // Get token from different sources
            $token = $request->bearerToken();
            Log::info('Bearer token present: ' . ($token ? 'Yes' : 'No'));
            
            if (!$token && $request->has('token')) {
                $token = $request->input('token');
                Log::info('Token from request input: ' . ($token ? 'Yes' : 'No'));
            }
            
            if (!$token) {
                Log::warning('No token provided in request');
                return response()->json(['message' => 'Token not provided'], 401);
            }
            
            // Log token length for debugging (don't log the actual token for security)
            Log::info('Token length: ' . strlen($token));
            
            // Check if JWT_SECRET is set
            $key = env('JWT_SECRET');
            if (empty($key)) {
                Log::error('JWT_SECRET is not set in environment');
                return response()->json(['message' => 'Server configuration error'], 500);
            }
            
            // Validate token format before attempting to decode
            if (substr_count($token, '.') !== 2) {
                Log::error('Invalid token format: Token does not have three segments');
                return response()->json(['message' => 'Invalid token format', 'error' => 'Token must be in JWT format (header.payload.signature)'], 400);
            }
            
            Log::info('Attempting to decode JWT token');
            try {
                // Validate the token before storing it
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
                Log::info('JWT token successfully decoded');
            } catch (\UnexpectedValueException $e) {
                Log::error('JWT decode error: ' . $e->getMessage());
                return response()->json(['message' => 'Invalid token', 'error' => $e->getMessage()], 400);
            }
            
            // Try to store in session if available
            $sessionStored = false;
            try {
                if ($request->hasSession()) {
                    Log::info('Session is available, storing token');
                    $request->session()->put('jwt_token', $token);
                    $sessionStored = true;
                } else {
                    Log::warning('Session is not available, using cookie only');
                }
            } catch (\Exception $e) {
                Log::warning('Failed to store in session: ' . $e->getMessage() . '. Using cookie only.');
            }
            
            Log::info('Token stored successfully' . ($sessionStored ? ' in session and cookie' : ' in cookie only'));
            
            // Create a proper Cookie object with improved settings
            Log::info('Creating Cookie object with improved settings');
            $cookie = new \Symfony\Component\HttpFoundation\Cookie(
                'jwt_token',    // name
                $token,         // value
                time() + 3600,  // expire (1 hour)
                '/',           // path - set to root path to be available everywhere
                null,          // domain - null uses the current domain
                request()->secure(), // secure - true if HTTPS
                true,          // httpOnly - true to prevent JavaScript access
                false,         // raw
                'lax'          // sameSite - 'lax' for better compatibility
            );
            
            Log::info('Cookie settings: path=/, secure=' . (request()->secure() ? 'true' : 'false') . ', httpOnly=true, sameSite=lax');
            
            // Create response with JSON
            $response = response()->json([
                'message' => 'Token stored successfully' . ($sessionStored ? ' in session and cookie' : ' in cookie only'),
                'session_used' => $sessionStored
            ], 200);
            
            // Add cookie to response
            Log::info('Adding cookie to response');
            return $response->withCookie($cookie);
        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::error('Token expired: ' . $e->getMessage());
            return response()->json(['message' => 'Token has expired'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Log::error('Invalid token signature: ' . $e->getMessage());
            return response()->json(['message' => 'Invalid token signature'], 401);
        } catch (\Exception $e) {
            Log::error('Failed to store token: ' . $e->getMessage());
            Log::error('Exception class: ' . get_class($e));
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Failed to store token', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get the authenticated user's details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        // The authenticated user is available via $request->user() due to the JWT middleware
        return response()->json($request->user());
    }

    /**
     * Generate a JWT token for the given user.
     *
     * @param User $user
     * @return string
     */
    protected function generateJwtToken(User $user)
    {
        $key = env('JWT_SECRET'); // Use a strong, unique secret key from .env
        $issuer = env('APP_URL', 'http://localhost'); // Your application URL
        $audience = env('APP_URL', 'http://localhost'); // Your application URL
        $issuedAt = time();
        $expirationTime = $issuedAt + (env('JWT_EXPIRE_HOUR', 1) * 60 * 60); // Token valid for 1 hour

        $payload = [
            'iss' => $issuer, // Issuer
            'aud' => $audience, // Audience
            'iat' => $issuedAt, // Issued at: time when the token was generated
            'exp' => $expirationTime, // Expiration time
            'nbf' => $issuedAt, // Not Before: token is valid from this time
            'sub' => $user->id, // Subject: user ID
            'email' => $user->email, // Custom data
        ];

        return JWT::encode($payload, $key, 'HS256');
    }
}