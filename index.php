<?php

use LearnApi\Apis;

require_once "Apis.php";

if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Login First.']);
    exit;
}


try {
    $api = new Apis();
    $userContacts =  $api->getUserContacts((int) $_SESSION['user_id']);

    if (empty($userContacts)) {
        http_response_code(404);
        echo json_encode(['error' => 'No contacts found for the user']);
        exit;
    }
    header('Content-type: JSON');
    echo json_encode($userContacts, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
