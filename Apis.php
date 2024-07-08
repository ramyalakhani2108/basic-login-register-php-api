<?php

declare(strict_types=1);

namespace LearnApi;

use Exception;
use LearnApi\Database;
use PDO;

class Apis
{
    private Database $db;
    private array $passErrors = [];
    public function __construct()
    {
        $this->db = new Database('mysql', [
            'host' => 'localhost',
            'port' => '3306',
            'dbname' => 'contact_direcotry',
        ], 'root', '');
    }

    public function getInstance()
    {
        return $this->db;
    }

    public function checkMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }
    }
    public function validateFields()
    {
        $requiredFields = ['username', 'first_name', 'last_name', 'email', 'password', 'phone'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required field: ' . $field]);
                exit;
            }
        }
    }

    public function validatePass(string $password): bool
    {

        if (strlen($password) < 8) {
            $this->passErrors[] = "Password must be at least 8 characters long.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->passErrors[] = "Password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->passErrors[] = "Password must contain at least one lowercase letter.";
        }
        if (!preg_match('/\d/', $password)) {
            $this->passErrors[] = "Password must contain at least one digit.";
        }
        if (!preg_match('/[!@#$%^&*()-_=+{};:,<.>]/', $password)) {
            $this->passErrors[] = "Password must contain at least one special character.";
        }
        if (preg_match('/[\s]/', $password)) {
            $this->passErrors[] = "Password cannot contain spaces.";
        }
        if (strip_tags($password) !== $password) {
            $this->passErrors[] = "Password cannot contain HTML tags.";
        }
        // print_r(empty($this->passErrors));
        return empty($this->passErrors);
    }

    public function getMessage(): string
    {

        $formattedErrors = array_map(function ($item) {
            return $item . "";
        }, $this->passErrors);

        // dd(implode("\n", $formattedErrors));
        return implode("", $formattedErrors);
    }

    public function insertData(array $data)
    {
        $sql = "INSERT INTO users (username,first_name, last_name, email, password, phone_no) VALUES (:uname,:fn, :ln, :eml,  :pass, :phn)";
        try {
            $this->db->query($sql, [
                'uname' => $data['username'],
                'fn' => $data['first_name'],
                'ln' => $data['last_name'],
                'eml' => $data['email'],
                'pass' => $data['password'],
                'phn' => $data['phone']
            ]);
            session_start();
            $_SESSION['user_id'] = $this->db->id();
        } catch (Exception $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Error registering user']);
            exit;
        }
    }




    public function validateLogin(array $data): bool
    {
        $user = $data['username'];
        $pass = $data['password'];
        if (empty($user || $pass)) {
            echo json_encode(['error' => 'Enter all the required Fields. ']);
            return false;
        }
        return true;
    }



    public function login($data)
    {
        $query = "SELECT * FROM users WHERE username=:un";
        $user = $this->db->query($query, [
            'un' => $data['username'],
        ])->find();


        $passwordsMatch = password_verify($data['password'], $user['password']);

        if (!$user || !$passwordsMatch) {
            echo json_encode(['error' => 'Check your Credentials']);
            exit;
        }
        // session_regenerate_id();
        $_SESSION['user_id'] = $user['id'];
    }

    public function validatePhone(string $phone): bool
    {

        // Remove all non-digit characters (allowing only digits and optional leading +)
        $cleanedNumber = preg_replace('/[^0-9+]/', '', $phone);
        // print_r($cleanedNumber);
        // Validate the format: optionally starting with + followed by digits
        if (preg_match('/^\+?\d+$/', $cleanedNumber) || strlen($cleanedNumber) === 10) {
            return true; // Valid mobile number format
        } else {
            return false; // Invalid mobile number format
        }
    }
}
