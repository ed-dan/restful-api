<?php

namespace config;

use \exceptions\InvalidSignatureException;
use \exceptions\TokenExpireException;

class JWTCodec
{
    public function __construct(private string $key)
    {
    }

    public function encode(array $payload): string 
    {
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);

        $header = $this->base64UrlEncode($header);

        $payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac("sha256", $header . "." . $payload, $this->key , true);
        $signature = $this->base64UrlEncode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    public function decode(string $token): array 
    {
        if (preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/", $token, $matches) !== 1){
                
            throw new \InvalidArgumentException("invalid token format");
        }

        $signature = hash_hmac("sha256", $matches["header"] . "." . $matches["payload"], $this->key, true);

        $signature_from_token = $this->base64UrlDecode($matches["signature"]);
        
        if (! hash_equals($signature, $signature_from_token)) {

            throw new InvalidSignatureException;
        }

        $payload = json_decode($this->base64UrlDecode($matches["payload"]), true);

        if ($payload["exp"] < time()){

            throw new TokenExpireException;
        }

        return $payload;
    }

    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }

    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text)
        );
    }
}









