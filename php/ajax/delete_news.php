<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$news_id = isset($input['news_id']) ? (int)$input['news_id'] : 0;

if (!$news_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid news ID']);
    exit();
}

// Get news data
$news = JsonHandler::readData(NEWS_FILE);
if ($news === null) {
    echo json_encode(['success' => false, 'error' => 'Failed to read news data']);
    exit();
}

// Filter out the news article to delete
$news = array_filter($news, function($article) use ($news_id) {
    return $article['id'] !== $news_id;
});

// Reindex array
$news = array_values($news);

// Save updated news data
if (JsonHandler::writeData(NEWS_FILE, $news)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete news']);
}
