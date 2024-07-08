<?php

use LearnApi\Apis;

include_once "Apis.php";
include_once "Database.php";

header('Content-Type: application/json');
$api = new Apis();

$api->checkMethod();

$api->validateFields();
$first_name = htmlspecialchars($_POST['first_name']);
$username = htmlspecialchars($_POST['username']);
$last_name = htmlspecialchars($_POST['last_name']);
$email = htmlspecialchars($_POST['email']);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);

$password = htmlspecialchars($_POST['password']);

$validatePass = $api->validatePass($password); //common validating password

$formattedErrors = $api->getMessage();
if (!empty($formattedErrors)) {
    echo json_encode(['errors' => $formattedErrors]);
    exit;
}

$phone = htmlspecialchars($_POST['phone']);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$data = [
    'username' => $username,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'email' => $email,
    'password' => $hashedPassword,
    'phone' => $phone
];


$api->insertData($data);


$response = [
    'success' => true,
    'message' => 'User registered successfully',

];
echo json_encode($response);
