
<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get games data
$games = JsonHandler::readData(GAMES_FILE);
if ($games === null) {
    $games = [];
}

// Get user's favorites if logged in
$user_favorites = [];
if (isset($_SESSION['user_id'])) {
    $users = JsonHandler::readData(USERS_FILE);
    if ($users !== null) {
        foreach ($users as $user) {
            if ($user['id'] === $_SESSION['user_id']) {
                $user_favorites = $user['favorites'];
                break;
            }
        }
    }
}

// Search filter
$search_name = trim(filter_input(INPUT_GET, 'search_name', FILTER_SANITIZE_STRING)) ?? '';
$search_category = trim(filter_input(INPUT_GET, 'search_category', FILTER_SANITIZE_STRING)) ?? '';

// Filter games by search
if ($search_name !== '' || $search_category !== '') {
    $games = array_filter($games, function($game) use ($search_name, $search_category) {
        $match_name = true;
        $match_category = true;

        if ($search_name !== '') {
            $match_name = mb_stripos($game['title'], $search_name) !== false;
        }

        if ($search_category !== '') {
            if (isset($game['categories']) && is_array($game['categories'])) {
                $match_category = false;
                foreach ($game['categories'] as $cat) {
                    if (mb_stripos($cat, $search_category) !== false) {
                        $match_category = true;
                        break;
                    }
                }
            } else {
                $match_category = mb_stripos($game['category'], $search_category) !== false;
            }
        }

        return $match_name && $match_category;
    });
}

// Sort games by title
usort($games, function($a, $b) {
    return strcasecmp($a['title'], $b['title']);
});

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Игротека</h1>
        <?php if (is_admin()): ?>
            <a href="/php/admin/game-create.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Добавить игру
            </a>
        <?php endif; ?>
    </div>

    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-center">
        <input type="text" name="search_name" placeholder="Поиск по названию" value="<?php echo htmlspecialchars($search_name); ?>"
               class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm">
        <input type="text" name="search_category" placeholder="Поиск по категории" value="<?php echo htmlspecialchars($search_category); ?>"
               class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-black focus:border-black text-sm">
        <button type="submit" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
            Поиск
        </button>
    </form>

    <?php if (empty($games)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500">Игр пока нет.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($games as $game): ?>
                <div class="bg-white shadow-sm rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <div class="aspect-w-16 aspect-h-9">
                        <img src="<?php echo htmlspecialchars($game['image']); ?>" 
                             alt="<?php echo htmlspecialchars($game['title']); ?>"
                             class="w-full h-48 object-cover">
                    </div>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-xl font-semibold text-gray-900">
                                <?php echo htmlspecialchars($game['title']); ?>
                            </h2>
                            <?php if (is_admin()): ?>
                                <div class="flex space-x-2">
                                    <a href="/php/admin/game-edit.php?id=<?php echo $game['id']; ?>" 
                                       class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button onclick="deleteGame(<?php echo $game['id']; ?>)" 
                                            class="text-gray-400 hover:text-red-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="prose prose-sm max-w-none mb-4">
                            <p class="text-gray-600">
                                <?php 
                                    $desc = $game['description'];
                                    if (mb_strlen($desc) > 100) {
                                        echo htmlspecialchars(mb_substr($desc, 0, 100, 'UTF-8')) . '...';
                                    } else {
                                        echo htmlspecialchars($desc);
                                    }
                                ?>
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                if (isset($game['categories']) && is_array($game['categories'])) {
                                    foreach ($game['categories'] as $cat) {
                                        echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">' . 
                                             htmlspecialchars($cat) . 
                                             '</span>';
                                    }
                                } else {
                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">' . 
                                         htmlspecialchars($game['category']) . 
                                         '</span>';
                                }
                                ?>
                            </div>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php if (in_array($game['id'], $user_favorites)): ?>
                                        <button onclick="removeFromFavorites(<?php echo $game['id']; ?>)" 
                                                class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-500">
                                            <svg class="h-5 w-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                            </svg>
                                            В избранном
                                        </button>
                                    <?php else: ?>
                                        <button onclick="addToFavorites(<?php echo $game['id']; ?>)" 
                                                class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-red-600">
                                            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                            Добавить в избранное
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <a href="/php/game-view.php?id=<?php echo $game['id']; ?>" 
                               class="inline-block text-sm font-medium text-black hover:text-gray-800">
                                Подробнее &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function addToFavorites(gameId) {
    fetch('/php/ajax/add_favorite.php', {
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
            alert('Произошла ошибка при добавлении игры в избранное');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка при добавлении игры в избранное');
    });
}

function removeFromFavorites(gameId) {
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

<?php if (is_admin()): ?>
function deleteGame(gameId) {
    if (confirm('Вы уверены, что хотите удалить эту игру?')) {
        fetch('/php/ajax/delete_game.php', {
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
                alert('Произошла ошибка при удалении игры');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении игры');
        });
    }
}
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
