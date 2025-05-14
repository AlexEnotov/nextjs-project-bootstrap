<?php
session_start();
define('INCLUDED', true);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/jsonHandler.php';

$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /php/profile.php');
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username && $password) {
        $users = JsonHandler::readData(USERS_FILE);
        
        if ($users !== null) {
            foreach ($users as $user) {
                if ($user['username'] === $username && password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Redirect to profile page
                    header('Location: /php/profile.php');
                    exit();
                }
            }
        }
        
        $error = 'Неверное имя пользователя или пароль';
    } else {
        $error = 'Пожалуйста, заполните все поля';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Вход в аккаунт
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Или
                <a href="/php/registration.php" class="font-medium text-black hover:text-gray-800">
                    зарегистрируйтесь
                </a>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="/php/login.php" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Имя пользователя</label>
                    <input id="username" name="username" type="text" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-black focus:border-black focus:z-10 sm:text-sm" 
                           placeholder="Имя пользователя">
                </div>
                <div>
                    <label for="password" class="sr-only">Пароль</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-black focus:border-black focus:z-10 sm:text-sm" 
                           placeholder="Пароль">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                    Войти
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
