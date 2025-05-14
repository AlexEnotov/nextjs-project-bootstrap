<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$image_id = isset($data['image_id']) ? (int)$data['image_id'] : 0;

if ($image_id) {
    // Get gallery data
    $gallery = JsonHandler::readData(GALLERY_FILE);
    if ($gallery !== null) {
        // Remove image with matching ID
        $gallery = array_filter($gallery, function($image) use ($image_id) {
            return $image['id'] !== $image_id;
        });
        
        // Reindex array
        $gallery = array_values($gallery);
        
        if (JsonHandler::writeData(GALLERY_FILE, $gallery)) {
            echo json_encode(['success' => true]);
            exit();
        }
    }
}

echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
