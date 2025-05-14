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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For simplicity, we will accept image URL input instead of file upload
    $image_url = filter_input(INPUT_POST, 'image_url', FILTER_VALIDATE_URL);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if ($image_url && $title && $description) {
        $gallery = JsonHandler::readData(GALLERY_FILE);
        if ($gallery === null) {
            $gallery = [];
        }

        // Generate new ID
        $max_id = 0;
        foreach ($gallery as $image) {
            if ($image['id'] > $max_id) {
                $max_id = $image['id'];
            }
        }

        // Create new image entry
        $new_image = [
            'id' => $max_id + 1,
            'title' => $title,
            'description' => $description,
            'url' => $image_url,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Add to gallery array
        $gallery[] = $new_image;

        if (JsonHandler::writeData(GALLERY_FILE, $gallery)) {
            $success = 'Изображение успешно добавлено';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Ошибка при сохранении изображения';
        }
    } else {
        $error = 'Пожалуйста, заполните все поля корректно';
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Загрузка изображения в галерею</h1>

    <?php if ($success): ?>
        <div class="mb-8 bg-green-50 border-l-4 border-green-400 p-4">
            <p class="text-green-700"><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="mb-8 bg-red-50 border-l-4 border-red-400 p-4">
            <p class="text-red-700"><?php echo $error; ?></p>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Название</label>
            <input type="text" name="title" id="title" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
            <textarea name="description" id="description" rows="4" required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"></textarea>
        </div>

        <div>
            <label for="image_url" class="block text-sm font-medium text-gray-700">URL изображения</label>
            <input type="url" name="image_url" id="image_url" required
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
            <p class="mt-1 text-sm text-gray-500">Введите прямую ссылку на изображение (например, https://example.com/image.jpg)</p>
        </div>

        <div>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Загрузить
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
