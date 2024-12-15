<?php

class TaskGateway
{
    private PDO $conn;
    
    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }
    
    public function getAllForUser(int $user_id): array
    {
        $sql = 'SELECT * FROM tasks WHERE user_id = :user_id ORDER BY name';
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $tasks = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
            $row['is_completed'] = (bool) $row['is_completed'];
            $tasks[] = $row;
        }

        return $tasks;
    }

    public function getForUser(int $user_id, string $id): array | false
    {
        $sql = 'SELECT * FROM tasks WHERE id = :id AND user_id = :user_id';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($task)
            $task['is_completed'] = (bool) $task['is_completed'];
        
        return $task;
    }

    public function createForUser(int $user_id, array $data): string
    {
        $sql = 'INSERT INTO tasks (name, priority, is_completed, user_id) 
                VALUES (:name, :priority, :is_completed, :user_id)';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

        if(empty($data['priority']))
            $stmt->bindValue(':priority', null, PDO::PARAM_NULL);
        else
            $stmt->bindValue(':priority', $data['priority'], PDO::PARAM_INT);
        
        $stmt->bindValue(':is_completed', $data['is_completed'] ?? false, PDO::PARAM_BOOL);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function updateForUser(int $user_id, string $id, array $task): int 
    {
        $fields = [];

        if (!empty($task["name"])) {
            $fields["name"] = [
                $task["name"],
                PDO::PARAM_STR,
            ];
        }

        if (array_key_exists("priority", $task)) {
            $fields["priority"] = [
                $task["priority"],
                $task["priority"] === null  ? PDO::PARAM_NULL : PDO::PARAM_INT,
            ];
        }
        
        if (array_key_exists("is_completed", $task)) {
            $fields["is_completed"] = [
                $task["is_completed"],
                PDO::PARAM_BOOL,
            ];
        }
        
        if (empty($fields)) {
            return 0;
        } else {
            $sets = array_map(function($value) {
                return "$value = :$value";
            }, array_keys($fields));
    
            $sql = "UPDATE tasks" . " SET " . implode(", ", $sets) . " WHERE id = :id AND user_id = :user_id";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        foreach($fields as $key => $values) {
            $stmt->bindValue(":$key", $values[0], $values[1]);

        }
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function deleteForUser(int $user_id, string $id): int 
    {
        $sql = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";

        $stmt= $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

}