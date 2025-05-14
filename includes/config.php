<?php
// Prevent direct access to this file
if (!defined('INCLUDED')) {
    die('Direct access not permitted');
}

// Define constants for data file paths
define('DATA_PATH', __DIR__ . '/../data/');
define('USERS_FILE', DATA_PATH . 'users.json');
define('GAMES_FILE', DATA_PATH . 'games.json');
define('NEWS_FILE', DATA_PATH . 'news.json');
define('FORUM_FILE', DATA_PATH . 'forum.json');
define('GALLERY_FILE', DATA_PATH . 'gallery.json');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Site configuration
define('SITE_NAME', 'Euporia');
define('SITE_URL', 'http://localhost:8000');

// Admin configuration
define('ADMIN_EMAIL', 'admin@example.com');

// Function to sanitize output
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Configure session settings before session_start
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
