<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/jsonHandler.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !is_admin()) {
    header('Location: /php/forum.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $forum = JsonHandler::readData(FORUM_FILE);
    if ($forum === null) {
        $forum = ['sections' => [], 'topics' => []];
    }
    if (!isset($forum['sections'])) {
        $forum['sections'] = [];
    }

    if ($action === 'add') {
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        
        if ($name && $description) {
            $newSection = [
                'id' => time(), // Using timestamp as ID
                'name' => $name,
                'description' => $description
            ];
            $forum['sections'][] = $newSection;
            JsonHandler::writeData(FORUM_FILE, $forum);
        }
    } elseif ($action === 'delete') {
        $section_id = filter_input(INPUT_POST, 'section_id', FILTER_VALIDATE_INT);
        if ($section_id) {
            $forum['sections'] = array_filter($forum['sections'], function($section) use ($section_id) {
                return $section['id'] !== $section_id;
            });
            JsonHandler::writeData(FORUM_FILE, $forum);
        }
    }
    
    header('Location: /php/admin/section-manage.php');
    exit();
}

// Get forum data
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum === null || !isset($forum['sections'])) {
    $forum = ['sections' => [], 'topics' => []];
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Управление разделами форума</h1>
        
        <!-- Add Section Form -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Добавить новый раздел</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Название раздела</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Описание</label>
                    <textarea name="description" id="description" rows="3" required
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm"></textarea>
                </div>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Добавить раздел
                </button>
            </form>
        </div>

        <!-- Sections List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6">
                <h2 class="text-lg font-medium text-gray-900">Существующие разделы</h2>
            </div>
            <?php if (empty($forum['sections'])): ?>
                <div class="px-4 py-5 sm:p-6">
                    <p class="text-gray-500">Разделов пока нет.</p>
                </div>
            <?php else: ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($forum['sections'] as $section): ?>
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($section['name']); ?></h3>
                                    <p class="mt-1 text-sm text-gray-600"><?php echo htmlspecialchars($section['description']); ?></p>
                                </div>
                                <form method="POST" class="flex-shrink-0">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">
                                    <button type="submit" onclick="return confirm('Вы уверены, что хотите удалить этот раздел?')"
                                            class="text-red-600 hover:text-red-800">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
