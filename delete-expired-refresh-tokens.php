<?php

declare(strict_types=1);

require __DIR__ . "/vendor/autoload.php";

use \config\Database;
use \gateways\RefreshTokenGateway;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

echo $refresh_token_gateway->deleteExpired(), "\n";