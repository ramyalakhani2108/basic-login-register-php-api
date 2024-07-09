<?php

declare(strict_types=1);

use LearnApi\Apis;

require_once "../Apis.php";

$api = new Apis();

$api->checkMethod();

if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Login First.']);
    exit;
}

$api->validateContacts();

$first_name = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : null;
$last_name = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : null;
$phone_no = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : null;
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;



if ($email !== null) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone_no,
            'id' => $_GET['id']
        ];
        $api->updateContact($data);
    }
} else {
    echo json_encode(['error' => 'Please enter email address']);
}
echo json_encode(['success' => 'updated_successfully ']);
header("Location: ../index.php");
