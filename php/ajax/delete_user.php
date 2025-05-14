<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

header('Content-Type: application/json');

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit();
}

// Prevent deleting self
if ($user_id === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Cannot delete own account']);
    exit();
}

// Get users data
$users = JsonHandler::readData(USERS_FILE);
if ($users === null) {
    echo json_encode(['success' => false, 'error' => 'Failed to read users data']);
    exit();
}

// Find user to delete
$user_found = false;
foreach ($users as $user) {
    if ($user['id'] === $user_id) {
        $user_found = true;
        break;
    }
}

if (!$user_found) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit();
}

// Filter out the user to delete
$users = array_values(
    array_filter($users, function($user) use ($user_id) {
        return $user['id'] !== $user_id;
    })
);

// Get forum data to remove user's topics and replies
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum !== null && isset($forum['topics'])) {
    // Remove user's topics
    $forum['topics'] = array_values(
        array_filter($forum['topics'], function($topic) use ($user) {
            return $topic['author'] !== $user['username'];
        })
    );
    
    // Remove user's replies from remaining topics
    foreach ($forum['topics'] as &$topic) {
        $topic['replies'] = array_values(
            array_filter($topic['replies'], function($reply) use ($user) {
                return $reply['author'] !== $user['username'];
            })
        );
    }
    
    JsonHandler::writeData(FORUM_FILE, $forum);
}

// Save updated users data
if (JsonHandler::writeData(USERS_FILE, $users)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
}
