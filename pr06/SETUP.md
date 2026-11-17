# Практическая работа № 4 · установка и этапы

Задание: `practice_04.pdf`; настройка Apache, PHP и Composer: `reference.pdf` (оба файла рядом с `starters/` в архиве). Среда: Windows, Uniform Server с MySQL, PHP 8.3–8.5 с `pdo_mysql` и `mbstring`, Composer 2. Веб-корень: только `public/` внутри проекта.

1. Скопируйте содержимое `starters/practice_04/` в `www\practice_04` относительно каталога установки Uniform Server. Это пример, можно выбрать другое место.
2. В phpMyAdmin импортируйте `sql/schema.sql`, затем `sql/variant_<буква>.sql` своего варианта.
3. Скопируйте `config.example.php` в `config.local.php` и укажите пользователя и пароль MySQL. Букву варианта укажите в `config.php`.
4. Укажите в учебном VirtualHost полный путь к `practice_04/public`. Адрес: `http://localhost:8080/`.
5. В корне проекта выполните `composer install`, затем проверьте `http://localhost:8080/health.php` (200, `stage`: 4, `pdo_mysql`: true).

## Этапы реализации

- `P04-CONNECTION`: `src/Infrastructure/Database.php`, подключение PDO к MySQL.
- `P04-COMPOSITION`: `bootstrap.php`, контроллер с зависимостями и пять маршрутов.
- `P04-ROUTING`: `src/Http/Router.php`, путь и метод, `id`, 404 и 405, HEAD и `Allow`.
- `P04-RESPONSE`: `src/Http/Application.php`, `src/Http/Response.php`, `X-Request-Id` и ответ без тела для HEAD.
- `P04-SQL`: `src/Repository/PdoItemRepository.php`, методы `find()`, `matching()`, `create()`.

Контролируемый ответ 501 с кодом этапа означает незавершённую реализацию, а не ошибку установки. Выполняйте этапы в указанном порядке.

После выполнения передайте исходники, `composer.json`, `composer.lock` и заполненный `README.md` (шаблон уже в проекте) со снимками экрана и файлом `self-check-result.md`. `vendor/`, `var/` и `config.local.php` не включайте. Письменный отчёт не требуется; Git необязателен, но рекомендуется.
