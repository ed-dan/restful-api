<?php

declare(strict_types=1);

use \config\{Database, JWTCodec};
use \gateways\RefreshTokenGateway;

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    header("Allow: POST");
    echo json_encode([
        "message" => "Allowed method: POST"
    ]);
    exit;
}

$user_data = (array) json_decode(file_get_contents("php://input"), true);

//var_dump($user_data);
if (! array_key_exists("token", $user_data)) {
    
    http_response_code(400);
    echo json_encode([
        "message" => "Missing token"
    ]);
    exit;
}

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

try {
    $payload = $codec->decode($user_data["token"]);
} catch (Exception) {

    http_response_code(400);
    echo json_encode([
        "message" => "Invalid token"
    ]);
    exit;
}

$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token_gateway->delete($user_data["token"]);
