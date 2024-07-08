<?php

use LearnApi\Apis;

include_once "Apis.php";
include_once "Database.php";

header('Content-Type: application/json');
$api = new Apis();

$api->checkMethod();


$username = htmlspecialchars($_POST['username']);


$password =  htmlspecialchars($_POST['password']);

$data = [
    'username' => $username,
    'password' => $password,

];

// $api->sanitizingData($data);

$validatedLogin = $api->validateLogin($data);

if ($validatedLogin) {
    $api->login($data);
    echo json_encode(['success' => 'logged in successfully ']);
} else {
    echo json_encode(['error' => 'Missing required field username or password ']);
}
