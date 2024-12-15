<?php

require dirname(__DIR__) . '/vendor/autoload.php';

set_error_handler('ErrorHandler::HandleError');
set_exception_handler('ErrorHandler::handleException');

Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

header('Content-type: application/json; charset=UTF-8');

$_SERVER["HTTP_AUTHORIZATION"] = $_SERVER["HTTP_AUTHORIZATION"] ?? "";
