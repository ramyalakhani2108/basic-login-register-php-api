<?php

declare(strict_types=1);

use LearnApi\Apis;

require_once "../Apis.php";

$api = new Apis();


if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Login First.']);
    exit;
}

$contacts = isset($_POST['contact_ids']) ? $_POST['contact_ids'] : [];

if (!$_SESSION['user_id'] || empty($contacts)) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID and Contact IDs array are required']);
    exit;
}
print_r($_POST['contact_ids']);

// isset($_POST['delete']) &&
if (isset($contacts)) {
    $api->delete($contacts);
} else {
    echo json_encode("Press delete button");
}
