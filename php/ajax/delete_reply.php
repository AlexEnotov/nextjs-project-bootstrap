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
$reply_id = isset($input['reply_id']) ? (int)$input['reply_id'] : 0;

if (!$topic_id || !$reply_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid topic or reply ID']);
    exit();
}

// Get forum data
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum === null || !isset($forum['topics'])) {
    echo json_encode(['success' => false, 'error' => 'Failed to read forum data']);
    exit();
}

// Find topic and reply
$topic_index = -1;
$reply_found = false;
foreach ($forum['topics'] as $index => $topic) {
    if ($topic['id'] === $topic_id) {
        $topic_index = $index;
        foreach ($topic['replies'] as $reply) {
            if ($reply['id'] === $reply_id) {
                // Verify ownership or admin status
                if ($reply['author'] !== $_SESSION['username'] && !is_admin()) {
                    echo json_encode(['success' => false, 'error' => 'Permission denied']);
                    exit();
                }
                $reply_found = true;
                break;
            }
        }
        break;
    }
}

if ($topic_index === -1) {
    echo json_encode(['success' => false, 'error' => 'Topic not found']);
    exit();
}

if (!$reply_found) {
    echo json_encode(['success' => false, 'error' => 'Reply not found']);
    exit();
}

// Filter out the reply to delete
$forum['topics'][$topic_index]['replies'] = array_values(
    array_filter($forum['topics'][$topic_index]['replies'], function($reply) use ($reply_id) {
        return $reply['id'] !== $reply_id;
    })
);

// Save updated forum data
if (JsonHandler::writeData(FORUM_FILE, $forum)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete reply']);
}
