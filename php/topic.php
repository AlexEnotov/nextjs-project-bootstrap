<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Function to get user role
function getUserRole($username) {
    $users = JsonHandler::readData(USERS_FILE);
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            switch ($user['role']) {
                case 'admin':
                    return 'Админ';
                case 'user':
                    return 'Пользователь';
                case 'member':
                    return 'Участник';
                default:
                    return $user['role'];
            }
        }
    }
    return 'Пользователь'; // Default role
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

// Handle new reply submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $reply_content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    
    if ($reply_content) {
        // Add new reply
        $new_reply = [
            'id' => count($topic['replies']) + 1,
            'content' => $reply_content,
            'author' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s'),
            'author_role' => $_SESSION['user_role'] ?? 'user'
        ];
        
        // Update topic in forum data
        foreach ($forum['topics'] as &$t) {
            if ($t['id'] === $topic_id) {
                $t['replies'][] = $new_reply;
                $topic = $t; // Update local topic variable
                break;
            }
        }
        
        if (JsonHandler::writeData(FORUM_FILE, $forum)) {
            $success = 'Ответ успешно добавлен';
        } else {
            $error = 'Ошибка при добавлении ответа';
        }
    } else {
        $error = 'Пожалуйста, введите текст ответа';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="/php/forum.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад к форуму
        </a>
    </div>

    <!-- Topic -->
    <div class="bg-white shadow sm:rounded-lg overflow-hidden mb-8">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-start">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">
                    <?php echo htmlspecialchars($topic['title']); ?>
                </h1>
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['username'] === $topic['author'] || is_admin())): ?>
                    <div class="flex space-x-4">
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

            <div class="flex items-center text-sm text-gray-500 mb-4">
                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span><?php echo htmlspecialchars($topic['author']); ?></span>
                <span class="px-2 py-0.5 ml-2 text-xs rounded bg-gray-100 text-gray-700">
                    <?php echo getUserRole($topic['author']); ?>
                </span>
                <span class="mx-2">&middot;</span>
                <time datetime="<?php echo $topic['created_at']; ?>">
                    <?php echo date('d.m.Y H:i', strtotime($topic['created_at'])); ?>
                </time>
            </div>

            <div class="prose max-w-none">
                <?php echo nl2br(htmlspecialchars($topic['content'])); ?>
            </div>
        </div>
    </div>

    <!-- Replies -->
    <div class="space-y-6">
        <h2 class="text-lg font-medium text-gray-900">
            Ответы (<?php echo count($topic['replies']); ?>)
        </h2>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
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

        <?php if ($success): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4">
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

        <?php foreach ($topic['replies'] as $reply): ?>
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span><?php echo htmlspecialchars($reply['author']); ?></span>
                            <span class="px-2 py-0.5 ml-2 text-xs rounded bg-gray-100 text-gray-700">
                                <?php echo getUserRole($reply['author']); ?>
                            </span>
                            <span class="mx-2">&middot;</span>
                            <time datetime="<?php echo $reply['created_at']; ?>">
                                <?php echo date('d.m.Y H:i', strtotime($reply['created_at'])); ?>
                            </time>
                        </div>
                        <?php if (isset($_SESSION['user_id']) && ($_SESSION['username'] === $reply['author'] || is_admin())): ?>
                            <button onclick="deleteReply(<?php echo $topic['id']; ?>, <?php echo $reply['id']; ?>)" 
                                    class="text-gray-400 hover:text-red-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 prose max-w-none">
                        <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Reply Form -->
            <form action="/php/topic.php?id=<?php echo $topic_id; ?>" method="POST" class="mt-8">
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">
                        Ваш ответ
                    </label>
                    <div class="mt-1">
                        <textarea id="content" name="content" rows="4" 
                                  class="shadow-sm block w-full focus:ring-black focus:border-black sm:text-sm border border-gray-300 rounded-md"
                                  required></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                        Ответить
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="mt-8 rounded-md bg-gray-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700">
                            Чтобы ответить, необходимо 
                            <a href="/php/login.php" class="font-medium text-black hover:text-gray-800">
                                войти
                            </a> 
                            или 
                            <a href="/php/registration.php" class="font-medium text-black hover:text-gray-800">
                                зарегистрироваться
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
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
                window.location.href = '/php/forum.php';
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

function deleteReply(topicId, replyId) {
    if (confirm('Вы уверены, что хотите удалить этот ответ?')) {
        fetch('/php/ajax/delete_reply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ topic_id: topicId, reply_id: replyId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Произошла ошибка при удалении ответа');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении ответа');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
