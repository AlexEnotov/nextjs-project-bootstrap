<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/jsonHandler.php';

// Get latest news
$news = JsonHandler::readData(NEWS_FILE);
if ($news === null) {
    $news = [];
}
$latest_news = array_slice($news, 0, 3);

// Get latest games
$games = JsonHandler::readData(GAMES_FILE);
if ($games === null) {
    $games = [];
}
// Get only latest 3 games
$latest_games = array_slice($games, 0, 3);

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="text-center py-16 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
            <span class="block">Добро пожаловать в</span>
            <span class="block text-black"><?php echo SITE_NAME; ?></span>
        </h1>
        <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
            Ваше сообщество для обсуждения игр, обмена опытом и поиска единомышленников.
        </p>
        <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="rounded-md shadow">
                    <a href="/php/registration.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-black hover:bg-gray-800 md:py-4 md:text-lg md:px-10">
                        Присоединиться
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Latest News Section -->
    <div class="mt-16">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Последние новости</h2>
            <a href="/php/news.php" class="text-black hover:text-gray-600">
                Все новости →
            </a>
        </div>
        <?php if (empty($latest_news)): ?>
            <p class="text-gray-500 text-center py-8">Новостей пока нет.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($latest_news as $article): ?>
                    <article class="bg-white shadow-sm rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <?php if (isset($article['image']) && $article['image']): ?>
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4">
                            <?php 
                            $preview = strip_tags($article['content']);
                            if (mb_strlen($preview, 'UTF-8') > 100) {
                                echo htmlspecialchars(mb_substr($preview, 0, 100, 'UTF-8')) . '...';
                            } else {
                                echo htmlspecialchars($preview);
                            }
                            ?>
                            </p>
                            <a href="/php/news-view.php?id=<?php echo $article['id']; ?>" 
                               class="text-black hover:text-gray-600">
                                Читать далее →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Latest Games Section -->
    <div class="mt-16">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Популярные игры</h2>
            <a href="/php/games.php" class="text-black hover:text-gray-600">
                Все игры →
            </a>
        </div>
        <?php if (empty($latest_games)): ?>
            <p class="text-gray-500 text-center py-8">Игр пока нет.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($latest_games as $game): ?>
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <img src="<?php echo htmlspecialchars($game['image']); ?>" 
                             alt="<?php echo htmlspecialchars($game['title']); ?>"
                             class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                <a href="/php/game-view.php?id=<?php echo $game['id']; ?>" class="hover:underline">
                                    <?php echo htmlspecialchars($game['title']); ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 mb-4">
                                <?php 
                                    $desc = $game['description'];
                                    if (mb_strlen($desc, 'UTF-8') > 100) {
                                        echo htmlspecialchars(mb_substr($desc, 0, 100, 'UTF-8')) . '...';
                                    } else {
                                        echo htmlspecialchars($desc);
                                    }
                                ?>
                            </p>
                            <div class="flex justify-between items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($game['category']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
