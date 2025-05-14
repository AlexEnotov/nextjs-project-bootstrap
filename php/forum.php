<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get forum data
$forum = JsonHandler::readData(FORUM_FILE);
if ($forum === null || !isset($forum['topics'])) {
    $forum = ['topics' => []];
}

$sections = $forum['sections'] ?? [];
$topics = $forum['topics'] ?? [];

// Group topics by section_id
$topics_by_section = [];
foreach ($sections as $section) {
    $topics_by_section[$section['id']] = [];
}
foreach ($topics as $topic) {
    $section_id = $topic['section_id'] ?? 0;
    if (!isset($topics_by_section[$section_id])) {
        $topics_by_section[$section_id] = [];
    }
    $topics_by_section[$section_id][] = $topic;
}

// Sort topics in each section by date (newest first)
foreach ($topics_by_section as &$section_topics) {
    usort($section_topics, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}
unset($section_topics);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Форум</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/php/forum-create.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Создать тему
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($topics)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500">Тем пока нет. Будьте первым, кто создаст тему для обсуждения!</p>
        </div>
    <?php else: ?>
        <?php foreach ($sections as $section): ?>
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($section['name']); ?></h2>
                <?php if (empty($topics_by_section[$section['id']])): ?>
                    <p class="text-gray-500">Тем в этом разделе пока нет.</p>
                <?php else: ?>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($topics_by_section[$section['id']] as $topic): ?>
                                <li>
                                    <div class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <a href="/php/topic.php?id=<?php echo $topic['id']; ?>" class="block focus:outline-none">
                                                    <p class="text-lg font-medium text-black truncate">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </p>
                                                    <div class="mt-2 flex items-center text-sm text-gray-500">
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        <span><?php echo htmlspecialchars($topic['author']); ?></span>
                                                        <span class="mx-2">&middot;</span>
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2h2v4l.586-.586z"/>
                                                        </svg>
                                                        <time datetime="<?php echo $topic['created_at']; ?>">
                                                            <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?>
                                                        </time>
                                                        <span class="mx-2">&middot;</span>
                                                        <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                                                        </svg>
                                                        <span><?php echo count($topic['replies']); ?> ответов</span>
                                                    </div>
                                                </a>
                                            </div>
                                            <?php if (isset($_SESSION['user_id']) && ($_SESSION['user_id'] === $topic['author'] || is_admin())): ?>
                                                <div class="ml-4 flex-shrink-0 flex items-center space-x-4">
                                                    <a href="/php/forum-edit.php?id=<?php echo $topic['id']; ?>" 
                                                       class="text-gray-400 hover:text-gray-500">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </a>
                                                    <button onclick="deleteTopic(<?php echo $topic['id']; ?>)" 
                                                            class="text-gray-400 hover:text-red-500">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-600 line-clamp-2">
                                                <?php 
                                                $preview = strip_tags($topic['content']);
                                                echo strlen($preview) > 200 ? substr($preview, 0, 200) . '...' : $preview;
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function deleteTopic(topicId) {
    if (confirm('Вы уверены, что хотите удалить эту тему?')) {
        fetch('/php/ajax/delete_topic.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ topic_id: topicId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Произошла ошибка при удалении темы');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении темы');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
