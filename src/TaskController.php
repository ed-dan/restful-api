<?php

class TaskController
{
    public function __construct(private TaskGateway $gateway, private int $user_id)
    {
    }
 
    public function processRequest(string $method, ?string $id): void 
    {
        if ($id === null) {
        
            if ($method == "GET") {
                echo json_encode($this->gateway->getAllForUser($this->user_id));

            } elseif ($method == "POST"){

                $new_task = (array) json_decode(file_get_contents("php://input"), true);
                $errors = $this->getValidationErrors($new_task);

                if (!empty($errors)){
                    $this->respomdUnprocessableEntity($errors);
                    return; 
                }

                $id = $this->gateway->createForUser($this->user_id, $new_task);
                $this->respondCreated($id);

            } else {
                $this->respondMethodNotAllowed("GET, POST");
            } 

        } else {

            $task = $this->gateway->getForUser($this->user_id, $id);

            if ($task === false){
                $this->respondNotFound($id);
                return;
            }
            
            switch ($method) {

                case "GET":

                    echo json_encode($task);
                    break;

                case "PATCH":

                    $update_task = (array) json_decode(file_get_contents("php://input"), true);
                    $errors = $this->getValidationErrors($update_task, false);

                    if (!empty($errors)){
                        $this->respomdUnprocessableEntity($errors);
                        return; 
                    }

                    $rows =  $this->gateway->updateForUser($this->user_id, $id, $update_task);

                    echo json_encode([
                        "message" => "Task with id: $id was updated",
                        "rows" => $rows
                        ]);

                    break;

                case "DELETE":

                    $rows =  $this->gateway->deleteForUser($this->user_id, $id);

                    echo json_encode([
                        "message" => "Task with id: $id was deleted",
                        "rows" => $rows
                        ]);
                    break;

                default:
                $this->respondMethodNotAllowed("GET, PATCH, DELETE");  
            }

        }
    }

    private function respomdUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode([
            "errors" => $errors,
        ]);
    }

    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
        echo json_encode([
           "message" => "Allowed methods $allowed_methods"
        ]);
    }

    private function respondNotFound(string $id): void 
    {
        http_response_code(404);
        echo json_encode([
            "message" => "task with id: $id not found"
        ]);
    }

    private function respondCreated(string $id): void 
    {
        http_response_code(201);
        echo json_encode([
            "message" => "task was successfully created with id: $id"
        ]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array 
    {
        $errors = [];

        if (empty($data['name']) and $is_new) 
            $errors[] = 'name is required';

        if (!empty($data['priority'])) {

            if(!is_int($data['priority'])) 
                $errors[] = 'priority must be an integer';
        }

        return $errors;
    }

}








