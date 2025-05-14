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
$game_id = isset($input['game_id']) ? (int)$input['game_id'] : 0;

if (!$game_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid game ID']);
    exit();
}

// Get games data
$games = JsonHandler::readData(GAMES_FILE);
if ($games === null) {
    echo json_encode(['success' => false, 'error' => 'Failed to read games data']);
    exit();
}

// Filter out the game to delete
$games = array_filter($games, function($game) use ($game_id) {
    return $game['id'] !== $game_id;
});

// Reindex array
$games = array_values($games);

// Remove game from all users' favorites
$users = JsonHandler::readData(USERS_FILE);
if ($users !== null) {
    $users_updated = false;
    foreach ($users as &$user) {
        if (in_array($game_id, $user['favorites'])) {
            $user['favorites'] = array_values(
                array_filter($user['favorites'], function($id) use ($game_id) {
                    return $id !== $game_id;
                })
            );
            $users_updated = true;
        }
    }
    
    if ($users_updated) {
        JsonHandler::writeData(USERS_FILE, $users);
    }
}

// Save updated games data
if (JsonHandler::writeData(GAMES_FILE, $games)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete game']);
}
