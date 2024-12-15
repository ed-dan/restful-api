<?php

class ErrorHandler
{
    public static function handleError(
        int $error_number, 
        string $erorr_message,
        string $error_file,
        int $error_line) : void
    {
        throw new ErrorException($erorr_message, 0, $error_number, $error_file, $error_line);
    }

    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        echo json_encode([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()  
        ]);
    }
}