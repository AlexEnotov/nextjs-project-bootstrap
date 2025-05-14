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
$topic_id = isset($input['topic_id']) ? (int)$input['topic_id'] : 0;

if (!$topic_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid topic ID']);
    exit();
}

// Get forum data
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum === null || !isset($forum['topics'])) {
    echo json_encode(['success' => false, 'error' => 'Failed to read forum data']);
    exit();
}

// Find topic and verify ownership or admin status
$topic_found = false;
foreach ($forum['topics'] as $topic) {
    if ($topic['id'] === $topic_id) {
        if ($topic['author'] !== $_SESSION['username'] && !is_admin()) {
            echo json_encode(['success' => false, 'error' => 'Permission denied']);
            exit();
        }
        $topic_found = true;
        break;
    }
}

if (!$topic_found) {
    echo json_encode(['success' => false, 'error' => 'Topic not found']);
    exit();
}

// Filter out the topic to delete
$forum['topics'] = array_values(
    array_filter($forum['topics'], function($topic) use ($topic_id) {
        return $topic['id'] !== $topic_id;
    })
);

// Save updated forum data
if (JsonHandler::writeData(FORUM_FILE, $forum)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete topic']);
}
