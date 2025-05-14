<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: /');
    exit();
}

// Get game ID from URL
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$game_id) {
    header('Location: /php/games.php');
    exit();
}

// Get games data
$games = JsonHandler::readData(GAMES_FILE);
if ($games === null) {
    header('Location: /php/games.php');
    exit();
}

// Find the specific game
$game = null;
foreach ($games as $g) {
    if ($g['id'] === $game_id) {
        $game = $g;
        break;
    }
}

if (!$game) {
    header('Location: /php/games.php');
    exit();
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $image = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);
    
    if ($title && $description && $category && $image) {
        // Process categories
        $categories = array_map('trim', explode(',', $category));
        $categories = array_filter($categories); // Remove empty categories
        
        // Update game in games data
        foreach ($games as &$g) {
            if ($g['id'] === $game_id) {
                $g['title'] = $title;
                $g['description'] = $description;
                $g['categories'] = $categories;
                $g['image'] = $image;
                break;
            }
        }
        
        if (JsonHandler::writeData(GAMES_FILE, $games)) {
            $success = 'Игра успешно обновлена';
            $game['title'] = $title;
            $game['description'] = $description;
            $game['categories'] = $categories;
            $game['image'] = $image;
        } else {
            $error = 'Ошибка при обновлении игры';
        }
    } else {
        $error = 'Пожалуйста, заполните все поля';
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Редактирование игры</h1>
        <a href="/php/games.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад к играм
        </a>
    </div>

    <?php if ($success): ?>
        <div class="mb-8 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?php echo $success; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-8 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo $error; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg">
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Название игры
                    </label>
                    <input type="text" name="title" id="title" 
                           value="<?php echo htmlspecialchars($game['title']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                           required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Описание
                    </label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                              required><?php echo htmlspecialchars($game['description']); ?></textarea>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">
                        Категории
                    </label>
                    <input type="text" name="category" id="category" 
                           value="<?php 
                           if (isset($game['categories'])) {
                               echo htmlspecialchars(implode(', ', $game['categories']));
                           } else {
                               echo htmlspecialchars($game['category']);
                           }
                           ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                           required>
                    <p class="mt-1 text-sm text-gray-500">
                        Укажите категории через запятую (например: Экшен, Приключения, RPG)
                    </p>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">
                        URL изображения
                    </label>
                    <input type="url" name="image" id="image" 
                           value="<?php echo htmlspecialchars($game['image']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                           required>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                        Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
