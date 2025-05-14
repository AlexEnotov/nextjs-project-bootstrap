<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get game ID from URL
$game_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$game_id) {
    header('Location: /php/games.php');
    exit();
}

// Get games data
$games = JsonHandler::readData(GAMES_FILE);
if ($games === null) {
    $games = [];
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

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="/php/games.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            &larr; Назад к игротеке
        </a>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="aspect-w-16 aspect-h-9">
            <img src="<?php echo htmlspecialchars($game['image']); ?>" 
                 alt="<?php echo htmlspecialchars($game['title']); ?>"
                 class="w-full h-64 object-cover">
        </div>
        <div class="p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($game['title']); ?></h1>
            <div class="flex gap-8">
                <div class="flex-1 prose max-w-none">
                    <p><?php echo nl2br(htmlspecialchars($game['description'])); ?></p>
                </div>
                <div class="w-64 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Статистика</h3>
                    <?php
                        // Count users who have this game in favorites
                        $users = JsonHandler::readData(USERS_FILE);
                        $favorites_count = 0;
                        foreach ($users as $user) {
                            if (isset($user['favorites']) && in_array($game['id'], $user['favorites'])) {
                                $favorites_count++;
                            }
                        }
                    ?>
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <button id="showParticipantsBtn" class="text-sm text-gray-600 underline hover:text-gray-800 focus:outline-none" type="button">
                            <?php 
                            echo $favorites_count . ' ' . 
                                 ($favorites_count === 1 ? 'участник' : 
                                 ($favorites_count >= 2 && $favorites_count <= 4 ? 'участника' : 'участников')); 
                            ?>
                        </button>
                    </div>
                    <div id="participantsList" style="display:none;">
                        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
                                <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-600 hover:text-gray-900 focus:outline-none" aria-label="Close modal">
                                    &times;
                                </button>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Участники</h3>
                                <ul class="max-h-64 overflow-y-auto text-sm text-gray-700">
                                    <?php
                                    foreach ($users as $user) {
                                        if (isset($user['favorites']) && in_array($game['id'], $user['favorites'])) {
                                            $discord = !empty($user['discord']) ? htmlspecialchars($user['discord']) : 'Не указан';
                                            echo '<li class="mb-2"><strong>' . htmlspecialchars($user['username']) . '</strong>: ' . $discord . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Категории:</h4>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('showParticipantsBtn');
    const modal = document.getElementById('participantsList');
    const closeBtn = document.getElementById('closeModalBtn');

    if (btn && modal && closeBtn) {
        btn.addEventListener('click', function() {
            modal.style.display = 'flex';
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        // Close modal on outside click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
