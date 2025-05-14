<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /php/login.php');
    exit();
}

// Get users data
$users = JsonHandler::readData(USERS_FILE);
if ($users === null) {
    $users = [];
}

// Find current user
$user = null;
foreach ($users as &$u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $user = &$u;
        break;
    }
}

if (!$user) {
    session_destroy();
    header('Location: /php/login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discord = trim(filter_input(INPUT_POST, 'discord', FILTER_SANITIZE_STRING));
    $telegram = trim(filter_input(INPUT_POST, 'telegram', FILTER_SANITIZE_STRING));

    // Optional: Add validation for Discord and Telegram formats if needed

    // Update user data
    $user['discord'] = $discord;
    $user['telegram'] = $telegram;

    if (JsonHandler::writeData(USERS_FILE, $users)) {
        $success = 'Профиль успешно обновлен';
    } else {
        $error = 'Ошибка при сохранении данных профиля';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Редактирование профиля</h1>

    <?php if ($error): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
            <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label for="discord" class="block text-sm font-medium text-gray-700">Discord</label>
            <input type="text" name="discord" id="discord" 
                   value="<?php echo htmlspecialchars($user['discord'] ?? ''); ?>"
                   placeholder="Введите ваш Discord"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
        </div>

        <div>
            <label for="telegram" class="block text-sm font-medium text-gray-700">Telegram</label>
            <input type="text" name="telegram" id="telegram" 
                   value="<?php echo htmlspecialchars($user['telegram'] ?? ''); ?>"
                   placeholder="Введите ваш Telegram"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Сохранить изменения
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
