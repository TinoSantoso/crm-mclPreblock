<?php
require_once __DIR__.'/vendor/autoload.php';

use Firebase\JWT\JWT;

// Load environment variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a payload similar to the one in AuthController::generateJwtToken
$key = $_ENV['JWT_SECRET'];
$issuer = $_ENV['APP_URL'] ?? 'http://localhost';
$audience = $_ENV['APP_URL'] ?? 'http://localhost';
$issuedAt = time();
$expirationTime = $issuedAt + (($_ENV['JWT_EXPIRE_HOUR'] ?? 1) * 60 * 60);

$payload = [
    'iss' => $issuer,
    'aud' => $audience,
    'iat' => $issuedAt,
    'exp' => $expirationTime,
    'nbf' => $issuedAt,
    'sub' => 1, // User ID 1
    'email' => 'test@example.com',
];

// Generate the JWT token
$token = JWT::encode($payload, $key, 'HS256');

echo "Generated JWT Token:\n";
echo $token . "\n";
echo "\nUse this token for testing:\n";
echo "curl -X POST http://localhost:8000/api/auth/store-token -H \"Content-Type: application/json\" -d '{\"token\": \"$token\"}' -v\n";