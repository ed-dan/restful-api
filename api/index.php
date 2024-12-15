<?php
 
declare(strict_types = 1);

require __DIR__ . "/bootstrap.php";

$database = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);
$user_gateway = new UserGateway($database);
$task_gateway = new TaskGateway($database);

$codec = new JWTCodec($_ENV["SECRET_KEY"]);

$auth = new Auth($user_gateway, $codec);

if (!$auth->validateUrlPath() or !$auth->authenticateAccessToken())
    exit;

$user_id = $auth->getUserId();

$controller = new TaskController($task_gateway, $user_id);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $auth->url_id);

