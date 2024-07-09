<?php

use LearnApi\Apis;

include_once "../Apis.php";

header('Content-Type: application/json');
$api = new Apis();

$api->checkMethod();

if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Login First.']);
    exit;
}
// $api->validateFields();
$first_name = isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : null;
$last_name = isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : null;
$phone_no = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : null;
$email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : null;

$phone = htmlspecialchars($_POST['phone']);
$validatePhone = $api->validatePhone($phone);
// echo $validatePhone;
if (!$validatePhone) {
    echo json_encode(['errors' => 'Please check entered phone number']);
}

if ($email !== null) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone_no,
        ];
        $api->createContact($data);
    }
} else {
    echo json_encode(['error' => 'Please enter email address']);
}
