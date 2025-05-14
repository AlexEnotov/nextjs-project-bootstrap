<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get news ID from URL
$news_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$news_id) {
    header('Location: /php/news.php');
    exit();
}

// Get news data
$news = JsonHandler::readData(NEWS_FILE);
if ($news === null) {
    header('Location: /php/news.php');
    exit();
}

// Find the specific news article
$article = null;
foreach ($news as $item) {
    if ($item['id'] === $news_id) {
        $article = $item;
        break;
    }
}

if (!$article) {
    header('Location: /php/news.php');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <a href="/php/news.php" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Назад к новостям
        </a>
    </div>

    <article class="bg-white shadow-sm rounded-lg overflow-hidden">
        <?php if (isset($article['image']) && $article['image']): ?>
            <div class="aspect-w-16 aspect-h-9">
                <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                     class="w-full h-96 object-cover">
            </div>
        <?php endif; ?>

        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($article['title']); ?>
                </h1>
                <?php if (is_admin()): ?>
                    <div class="flex space-x-2">
                        <a href="/php/admin/news-edit.php?id=<?php echo $article['id']; ?>" 
                           class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex items-center space-x-4 text-sm text-gray-500 mb-8">
                <div class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <?php echo htmlspecialchars($article['author']); ?>
                </div>
                <div class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <time datetime="<?php echo $article['created_at']; ?>">
                        <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                    </time>
                </div>
            </div>

            <div class="prose prose-lg max-w-none">
                <?php echo nl2br(htmlspecialchars($article['content'])); ?>
            </div>

            <?php if (is_admin()): ?>
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="flex justify-end space-x-4">
                        <a href="/php/admin/news-edit.php?id=<?php echo $article['id']; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                            Редактировать
                        </a>
                        <button onclick="deleteNews(<?php echo $article['id']; ?>)" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Удалить
                        </button>
                    </div>
                </div>

                <script>
                function deleteNews(newsId) {
                    if (confirm('Вы уверены, что хотите удалить эту новость?')) {
                        fetch('/php/ajax/delete_news.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ news_id: newsId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = '/php/news.php';
                            } else {
                                alert('Произошла ошибка при удалении новости');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Произошла ошибка при удалении новости');
                        });
                    }
                }
                </script>
            <?php endif; ?>
        </div>
    </article>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
