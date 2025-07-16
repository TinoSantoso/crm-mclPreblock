<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // For JWT, logout is typically handled client-side by deleting the token.
        // On the server, you might blacklist tokens if you have a robust system,
        // but for stateless JWT, simply not sending the token is enough.
        return response()->json(['message' => 'Logged out successfully'], 200);
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