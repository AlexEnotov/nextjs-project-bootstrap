<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$game_id = isset($input['game_id']) ? (int)$input['game_id'] : 0;

if (!$game_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid game ID']);
    exit();
}

// Get users data
$users = JsonHandler::readData(USERS_FILE);
if ($users === null) {
    echo json_encode(['success' => false, 'error' => 'Failed to read users data']);
    exit();
}

// Find and update user's favorites
$updated = false;
foreach ($users as &$user) {
    if ($user['id'] === $_SESSION['user_id']) {
        // Remove game from favorites
        $user['favorites'] = array_values(
            array_filter($user['favorites'], function($id) use ($game_id) {
                return $id !== $game_id;
            })
        );
        $updated = true;
        break;
    }
}

if (!$updated) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit();
}

// Save updated users data
if (JsonHandler::writeData(USERS_FILE, $users)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update favorites']);
}
