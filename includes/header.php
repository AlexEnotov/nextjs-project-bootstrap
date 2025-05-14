<?php
if (!defined('INCLUDED')) {
    define('INCLUDED', true);
    session_start();
    require_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-gray-50 font-sans">
    <header class="bg-white shadow-sm">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-2xl font-bold text-gray-900">
                            <?php echo SITE_NAME; ?>
                        </a>
                    </div>
                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/php/news.php" 
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'news.php') !== false ? 'border-b-2 border-black' : ''; ?> 
                                  inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            Новости
                        </a>
                        <a href="/php/games.php"
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'games.php') !== false ? 'border-b-2 border-black' : ''; ?>
                                  inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            Игротека
                        </a>
                        <a href="/php/forum.php"
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'forum.php') !== false ? 'border-b-2 border-black' : ''; ?>
                                  inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            Форум
                        </a>
                        <a href="/php/gallery.php"
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'gallery.php') !== false ? 'border-b-2 border-black' : ''; ?>
                                  inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            Галерея
                        </a>
                        <a href="/php/about.php"
                           class="<?php echo strpos($_SERVER['PHP_SELF'], 'about.php') !== false ? 'border-b-2 border-black' : ''; ?>
                                  inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">
                            О нас
                        </a>
                    </div>
                </div>
                <!-- User Navigation -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <?php if (is_logged_in()): ?>
                        <div class="ml-3 relative flex items-center space-x-4">
                            <a href="/php/profile.php" class="text-sm font-medium text-gray-900 flex items-center space-x-2">
                                <span>Личный кабинет</span>
                                <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-700">
                                    <?php
                                        $role = $_SESSION['user_role'] ?? 'user';
                                        switch ($role) {
                                            case 'admin':
                                                echo 'Админ';
                                                break;
                                            case 'user':
                                                echo 'Пользователь';
                                                break;
                                            case 'member':
                                                echo 'Участник';
                                                break;
                                            default:
                                                echo htmlspecialchars($role);
                                        }
                                    ?>
                                </span>
                            </a>
                            <?php if (is_admin()): ?>
                                <a href="/php/admin.php" class="text-sm font-medium text-gray-900">
                                    Панель администратора
                                </a>
                            <?php endif; ?>
                            <a href="/php/logout.php" class="text-sm font-medium text-gray-900">
                                Выйти
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-4">
                            <a href="/php/login.php" class="text-sm font-medium text-gray-900">
                                Войти
                            </a>
                            <a href="/php/registration.php" class="text-sm font-medium text-gray-900">
                                Регистрация
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Mobile menu button -->
                <div class="flex items-center sm:hidden">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-black" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </nav>
        <!-- Mobile menu -->
        <div class="sm:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="/php/news.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Новости</a>
                <a href="/php/games.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Игротека</a>
                <a href="/php/forum.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Форум</a>
                <a href="/php/gallery.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Галерея</a>
                <a href="/php/about.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">О нас</a>
                <?php if (is_logged_in()): ?>
                    <a href="/php/profile.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Личный кабинет</a>
                    <?php if (is_admin()): ?>
                        <a href="/php/admin.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Панель администратора</a>
                    <?php endif; ?>
                    <a href="/php/logout.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Выйти</a>
                <?php else: ?>
                    <a href="/php/login.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Войти</a>
                    <a href="/php/registration.php" class="block pl-3 pr-4 py-2 text-base font-medium text-gray-700">Регистрация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
