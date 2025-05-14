<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: /php/login.php');
    exit();
}

// Get topic ID from URL
$topic_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$topic_id) {
    header('Location: /php/forum.php');
    exit();
}

// Get forum data
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum === null || !isset($forum['topics'])) {
    header('Location: /php/forum.php');
    exit();
}

// Find the specific topic
$topic = null;
foreach ($forum['topics'] as $t) {
    if ($t['id'] === $topic_id) {
        $topic = $t;
        break;
    }
}

if (!$topic) {
    header('Location: /php/forum.php');
    exit();
}

// Check if user is author or admin
if ($topic['author'] !== $_SESSION['username'] && !is_admin()) {
    header('Location: /php/forum.php');
    exit();
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $section_id = filter_input(INPUT_POST, 'section_id', FILTER_VALIDATE_INT);
    
    if ($title && $content && $section_id) {
        // Update topic in forum data
        foreach ($forum['topics'] as &$t) {
            if ($t['id'] === $topic_id) {
                $t['title'] = $title;
                $t['content'] = $content;
                $t['section_id'] = $section_id;
                $t['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        if (JsonHandler::writeData(FORUM_FILE, $forum)) {
            $success = 'Тема успешно обновлена';
            $topic['title'] = $title;
            $topic['content'] = $content;
        } else {
            $error = 'Ошибка при обновлении темы';
        }
    } else {
        $error = 'Пожалуйста, заполните все поля';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Редактирование темы</h1>
        <a href="/php/topic.php?id=<?php echo $topic_id; ?>" 
           class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад к теме
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
                    <label for="section_id" class="block text-sm font-medium text-gray-700">
                        Раздел <span class="text-red-500">*</span>
                    </label>
                    <select name="section_id" id="section_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
                        <option value="">Выберите раздел</option>
                        <?php
                        $forum_data = JsonHandler::readData(FORUM_FILE);
                        $sections = $forum_data['sections'] ?? [];
                        foreach ($sections as $section) {
                            $selected = ($topic['section_id'] ?? 0) === $section['id'] ? 'selected' : '';
                            echo '<option value="' . htmlspecialchars($section['id']) . '" ' . $selected . '>' . htmlspecialchars($section['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Заголовок темы <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" 
                           value="<?php echo htmlspecialchars($topic['title']); ?>"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                           required>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">
                        Содержание <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" id="content" rows="10"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"
                              required><?php echo htmlspecialchars($topic['content']); ?></textarea>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
