</main>
    <footer class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo SITE_NAME; ?></h3>
                    <p class="text-gray-600 mb-4">
                        Ваше сообщество для обсуждения игр, обмена опытом и поиска единомышленников.
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Быстрые ссылки</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="/php/news.php" class="text-gray-600 hover:text-gray-900">Новости</a>
                        </li>
                        <li>
                            <a href="/php/games.php" class="text-gray-600 hover:text-gray-900">Игротека</a>
                        </li>
                        <li>
                            <a href="/php/forum.php" class="text-gray-600 hover:text-gray-900">Форум</a>
                        </li>
                        <li>
                            <a href="/php/about.php" class="text-gray-600 hover:text-gray-900">О нас</a>
                        </li>
                    </ul>
                </div>

                <!-- Social Links -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Социальные сети</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-600 hover:text-gray-900">
                            <span class="sr-only">Telegram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.14-.308.27-.634.27l.206-3.04 5.54-5.01c.24-.22-.054-.34-.373-.12l-6.834 4.31-2.95-.92c-.64-.2-.653-.64.136-.954l11.54-4.45c.537-.2 1.006.13.87.942z"/>
                            </svg>
                        </a>
                        <a href="https://discord.gg/mRH7q8T4QK" class="text-gray-600 hover:text-gray-900" target="_blank">
                            <span class="sr-only">Discord</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-center text-gray-500 text-sm">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Все права защищены.
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile menu JavaScript -->
    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
