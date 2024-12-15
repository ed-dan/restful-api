<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);
    header("Allow: POST");
    echo json_encode([
        "message" => "Allowed method: POST"
    ]);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode('/', $path);
$resourse = $parts[2] ?? null;

if ($resourse != 'refresh') {
    http_response_code(404);
    echo json_encode([
        "message" => "Resourse not found"
    ]);
    exit;
}

$user_data = (array) json_decode(file_get_contents("php://input"), true);

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

$user_id = $payload["sub"];

$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token = $refresh_token_gateway->getByToken($user_data["token"]);

if ($refresh_token === false) {
    http_response_code(400);
    echo json_encode([
        "message" => "Invalid token (not on whitelist)"
    ]);
    exit;
}

$user_gateway = new UserGateway($database);

$user = $user_gateway->getByID($user_id);

if ($user === false) {
    http_response_code(401);
    echo json_encode([
        "message" => "Invalid authentication"
    ]);
    exit;
}

require __DIR__ . "/tokens.php";

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

$refresh_token_gateway->delete($user_data["token"]);

$refresh_token_gateway->create($refresh_token, $refresh_token_expiry);