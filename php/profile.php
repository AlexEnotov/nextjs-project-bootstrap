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

// Get user data
$users = JsonHandler::readData(USERS_FILE);
$user = null;
foreach ($users as $u) {
    if ($u['id'] === $_SESSION['user_id']) {
        $user = $u;
        break;
    }
}

if (!$user) {
    session_destroy();
    header('Location: /php/login.php');
    exit();
}

// Get favorite games
$games = JsonHandler::readData(GAMES_FILE);
$favorite_games = [];
if ($games !== null) {
    foreach ($games as $game) {
        if (in_array($game['id'], $user['favorites'])) {
            $favorite_games[] = $game;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Profile Header -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Личный кабинет</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Информация профиля</h3>
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Имя пользователя</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['username']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo htmlspecialchars($user['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Дата регистрации</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo date('d.m.Y', strtotime($user['registered'])); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Роль</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo $user['role'] === 'admin' ? 'Администратор' : 'Пользователь'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Discord</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo !empty($user['discord']) ? htmlspecialchars($user['discord']) : 'Не указан'; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telegram</dt>
                            <dd class="mt-1 text-lg text-gray-900"><?php echo !empty($user['telegram']) ? htmlspecialchars($user['telegram']) : 'Не указан'; ?></dd>
                        </div>
                        <div class="mt-6">
        <a href="/php/update_profile.php" 
           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
            Редактировать профиль
        </a>
    </div>
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Статистика</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <dt class="text-sm font-medium text-gray-500">Избранных игр</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900"><?php echo count($favorite_games); ?></dd>
                        </div>
                        <!-- Add more statistics as needed -->
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorite Games -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Избранные игры</h3>
            <?php if (empty($favorite_games)): ?>
                <p class="text-gray-500">У вас пока нет избранных игр.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($favorite_games as $game): ?>
                        <div class="game-card shadow-sm rounded-lg overflow-hidden">
                            <img src="<?php echo htmlspecialchars($game['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($game['title']); ?>"
                                 class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">
                                    <a href="/php/game-view.php?id=<?php echo $game['id']; ?>" class="hover:underline">
                                        <?php echo htmlspecialchars($game['title']); ?>
                                    </a>
                                </h4>
                                <p class="text-gray-600 text-sm mb-4">
                                    <?php 
                                        $desc = $game['description'];
                                        if (mb_strlen($desc) > 100) {
                                            echo htmlspecialchars(mb_substr($desc, 0, 100, 'UTF-8')) . '...';
                                        } else {
                                            echo htmlspecialchars($desc);
                                        }
                                    ?>
                                </p>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        <?php 
                                        if (isset($game['categories']) && is_array($game['categories'])) {
                                            echo htmlspecialchars(implode(', ', $game['categories']));
                                        } else {
                                            echo htmlspecialchars($game['category']);
                                        }
                                        ?>
                                    </span>
                                    <button onclick="removeFromFavorites(<?php echo $game['id']; ?>)" 
                                            class="text-sm text-red-600 hover:text-red-800">
                                        Удалить из избранного
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function removeFromFavorites(gameId) {
    if (confirm('Вы уверены, что хотите удалить эту игру из избранного?')) {
        fetch('/php/ajax/remove_favorite.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ game_id: gameId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Произошла ошибка при удалении игры из избранного');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении игры из избранного');
        });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
