<?php

use \gateways\UserGateway;
use \config\JWTCodec;


class Auth 
{
    public ?string $url_id;
    private int $user_id;
    
    public function __construct(private UserGateway $user_gateway, private JWTCodec $codec)
    {
    }

    public function authenticateAPIKey(): bool 
    {
        if (empty($_SERVER["HTTP_X_API_KEY"])){
    
            http_response_code(400);
            echo json_encode([
                "message" => "missing API KEY"
            ]);
        
            return false;
        }

        $api_key = $_SERVER["HTTP_X_API_KEY"];

        $user = $this->user_gateway->getByAPIKey($api_key);

        if($user === false){

            http_response_code(401);
            echo json_encode([
                "message" => "invalid API KEY"
            ]);
        
            return false;
        }

        $this->user_id = $user["id"];
        
        return true;
    }

    public function getUserId()
    {
        return $this->user_id;
    }

    public function validateUrlPath(): bool 
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $parts = explode('/', $path);
        $resourse = $parts[2] ?? null;
        $this->url_id = $parts[3] ?? null;
        $partFour = $parts[4] ?? null;

        if ($resourse != 'tasks' or $partFour != null) {

            http_response_code(404);
            echo json_encode([
                "message" => "Resourse not found"
            ]);
            return false;
        }

        return true;
    }

    public function authenticateAccessToken(): bool
    {
        if (! preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            http_response_code(400);
            echo json_encode([
                "message" => "Incomplete authorization header"
            ]);
            return false;
        }
        
        try {
            $user_data = $this->codec->decode($matches[1]);   
            
        } catch (TokenExpireException) {
            
            http_response_code(401);
            echo json_encode([
                "message" => "Token has expired"
            ]);
            return false;

        } catch (InvalidSignatureException) {
            
            http_response_code(401);
            echo json_encode([
                "message" => "Invalid signature"
            ]);
            return false; 

        } catch (Exception $e) {

            http_response_code(400);
            echo json_encode([
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine()
            ]);
            return false;
        } 

        $this->user_id = $user_data["sub"];
        
        return true;
    }
}