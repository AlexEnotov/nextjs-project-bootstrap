<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

// Get gallery images
$gallery_images = JsonHandler::readData(GALLERY_FILE);
if ($gallery_images === null) {
    $gallery_images = [];
}

// Sort by created date (newest first)
usort($gallery_images, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    .gallery-item {
        position: relative;
        aspect-ratio: 4/3;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Галерея</h1>
        <?php if (is_admin()): ?>
            <a href="/php/admin/gallery-upload.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                Загрузить изображение
            </a>
        <?php endif; ?>
    </div>

    <?php if (empty($gallery_images)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500">В галерее пока нет изображений.</p>
        </div>
    <?php else: ?>
        <div class="gallery-grid">
            <?php foreach ($gallery_images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                         alt="<?php echo htmlspecialchars($image['title']); ?>"
                         class="rounded-lg shadow-md">
                    <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-60 transition-opacity duration-300 flex items-center justify-center opacity-0 hover:opacity-100">
                        <div class="text-white text-center p-4">
                            <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($image['title']); ?></h3>
                            <p class="text-sm"><?php echo htmlspecialchars($image['description']); ?></p>
                            <p class="text-xs mt-2">
                                <?php echo date('d.m.Y', strtotime($image['created_at'])); ?>
                            </p>
                            <?php if (is_admin()): ?>
                                <button onclick="deleteImage(<?php echo $image['id']; ?>)"
                                        class="mt-4 inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Удалить
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (is_admin()): ?>
<script>
function deleteImage(imageId) {
    if (confirm('Вы уверены, что хотите удалить это изображение?')) {
        fetch('/php/ajax/delete_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ image_id: imageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Произошла ошибка при удалении изображения');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении изображения');
        });
    }
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
