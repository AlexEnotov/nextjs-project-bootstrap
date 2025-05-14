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

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $image = filter_input(INPUT_POST, 'image', FILTER_SANITIZE_URL);
    
    if ($title && $content) {
        // Get news data
        $news = JsonHandler::readData(NEWS_FILE);
        if ($news === null) {
            $news = [];
        }
        
        // Generate new ID
        $max_id = 0;
        foreach ($news as $article) {
            if ($article['id'] > $max_id) {
                $max_id = $article['id'];
            }
        }
        
        // Create new article
        $new_article = [
            'id' => $max_id + 1,
            'title' => $title,
            'content' => $content,
            'image' => $image ?: null,
            'author' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add to news array
        array_unshift($news, $new_article); // Add to beginning of array
        
        if (JsonHandler::writeData(NEWS_FILE, $news)) {
            header('Location: /php/news.php');
            exit();
        } else {
            $error = 'Ошибка при создании новости';
        }
    } else {
        $error = 'Пожалуйста, заполните обязательные поля';
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Создание новости</h1>
        <a href="/php/news.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад к новостям
        </a>
    </div>

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
                        Заголовок <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                           required>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">
                        Содержание <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" id="content" rows="10"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                              required></textarea>
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">
                        URL изображения
                    </label>
                    <input type="url" name="image" id="image" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">
                        Необязательно. Укажите прямую ссылку на изображение (например, https://example.com/image.jpg)
                    </p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                        Опубликовать
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
