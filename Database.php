<?php

declare(strict_types=1);

namespace LearnApi;

use PDO,
    PDOException,
    PDOStatement;

class Database
{
    private PDO $connection;
    private PDOStatement $stmt;

    public function getConnection()
    {
        return $this->connection;
    }
    public function __construct(string $driver, array $config, string $username, string $password)
    {
        $configuration = http_build_query(data: $config, arg_separator: ";"); //this will create configuration string
        $dsn = "{$driver}:{$configuration}"; //this will create valid DSN string for connecting with databases
        // echo "$dsn";
        // return;
        try {
            $this->connection = new PDO($dsn, $username, $password, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            // echo "we have connected with the database";
        } catch (PDOException $e) {
            echo json_encode(['Error' => 'Unable to connect Database']);
            die();
        }
    }
    public function query(string $query, array $params = []): Database
    {
        $this->stmt = $this->connection->prepare($query);
        $this->stmt->execute($params);

        // echo $this->stmt;

        return $this; //returning isntance to apply method chaining
    }

    public function id()
    {
        return $this->connection->lastInsertId();
    } //it gives last id inserted
    public function find()
    {
        return $this->stmt->fetch();
    }

    public function findAll()
    {
        return $this->stmt->fetchAll();
    }

    public function deletionTransaction(string $query, array $params)
    {
        // Prepare the statement
        $this->stmt = $this->connection->prepare($query);

        // Bind the IDs as parameters
        foreach ($params as $key => $id) {
            $this->stmt->bindValue(":id$key", $id); // Bind each ID as an integer
        }
        $this->stmt->bindValue(':uid', $_SESSION['user_id']); // Replace with actual user_id

        // Execute the statement
        $this->stmt->execute();
    }
}
