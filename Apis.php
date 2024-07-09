<?php

declare(strict_types=1);

namespace LearnApi;

include_once "Database.php";

use Exception;
use LearnApi\Database;
use PDO;
use PDOException;

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
    public function checkEmail(string $email)
    {
    }


    public function checkMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }
    }

    public function delete(array $ids)
    {
        $connection = $this->db->getConnection();
        try {
            $connection->beginTransaction();

            // Prepare the placeholders for named parameters
            $placeholders = [];
            foreach ($ids as $key => $id) {
                $placeholders[] = ":id$key";
            }
            $placeholdersString = implode(',', $placeholders);

            // Prepare the query with named parameters
            $query = "DELETE FROM contacts WHERE id IN ($placeholdersString) AND user_id = :uid";

            $this->db->deletionTransaction($query, $ids);

            // Commit the transaction
            $connection->commit();

            echo json_encode('Deleted Successfully');
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $connection->rollback();

            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
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


    public function getUserContacts(int $id): array
    {
        $query = "SELECT * FROM contacts WHERE user_id=:uid";
        $userContacts = $this->db->query($query, [
            'uid' => $id
        ])->findAll();

        return $userContacts;
    }

    public function validateContacts()
    {
        $requiredFields = ['first_name', 'phone'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required field: ' . $field]);
                exit;
            }
        }
    }
    public function getContact(string $id)
    {
        $query = "SELECT * FROM contacts WHERE id=:id";
        $userContacts = $this->db->query($query, [
            'id' => $id
        ])->find();

        return $userContacts;
    }
    public function createContact(array $data)
    {
        $query = "INSERT INTO contacts (first_name,last_name,phone_no,email,user_id) VALUES (:fn,:ln,:phn,:eml,:uid)";
        try {
            $this->db->query($query, [
                'fn' => $data['first_name'] ?? '',
                'ln' => $data['last_name'] ?? '',
                'phn' => $data['phone'] ?? '',
                'eml' => $data['email'] ?? '',
                // 'uid' => $_SESSION['user_id'],
                'uid' => 3
            ]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'check the fields again']);
        }
    }

    public function updateContact(array $data)
    {

        $contact = $this->getContact($data['id']);
        $query = "UPDATE contacts SET first_name=:fn,last_name=:ln,email=:eml,phone_no=:phn WHERE user_id = :uid AND id=:id";
        $this->db->query($query, [
            'fn' => $data['first_name'] ?? $contact['first_name'],
            'ln' => $data['last_name'] ?? $contact['last_name'],
            'phn' => $data['phone'] ?? $contact['phone_no'],
            'eml' => $data['email'] ?? $contact['email'],
            'uid' => $_SESSION['user_id'],
            // 'uid' => 3,
            'id' => $data['id']
        ]);
    }
}
