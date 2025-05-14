<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get news data
$news = JsonHandler::readData(NEWS_FILE);
if ($news === null) {
    $news = [];
}

// Sort news by date (newest first)
usort($news, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Новости</h1>
        <?php if (is_admin()): ?>
            <a href="/php/admin/news-create.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Добавить новость
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($news)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500">Новостей пока нет.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($news as $article): ?>
                <article class="bg-white shadow-sm rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <?php if (isset($article['image']) && $article['image']): ?>
                        <div class="aspect-w-16 aspect-h-9">
                            <img src="<?php echo htmlspecialchars($article['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="w-full h-48 object-cover">
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h2>
                            <?php if (is_admin()): ?>
                                <div class="flex space-x-2">
                                    <a href="/php/admin/news-edit.php?id=<?php echo $article['id']; ?>" 
                                       class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button onclick="deleteNews(<?php echo $article['id']; ?>)" 
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
                            <?php 
                            $content = $article['content'];
                            if (mb_strlen($content) > 200) {
                                $content = mb_substr($content, 0, 200, 'UTF-8') . '...';
                            }
                            echo nl2br(htmlspecialchars($content)); 
                            ?>
                        </div>

                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <div class="flex items-center space-x-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span><?php echo htmlspecialchars($article['author']); ?></span>
                            </div>
                            <time datetime="<?php echo $article['created_at']; ?>">
                                <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                            </time>
                        </div>

                        <div class="mt-4">
                            <a href="/php/news-view.php?id=<?php echo $article['id']; ?>" 
                               class="inline-flex items-center text-sm font-medium text-black hover:text-gray-700">
                                Читать далее
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (is_admin()): ?>
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
                location.reload();
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
