# Практическая работа № 3 · Исходный проект

Задание — `practice_03.pdf`; настройка Apache, PHP, Composer и Git — `reference.pdf` (оба файла рядом с `practice_03/` в ZIP).

1. Рекомендуемый пример относительно каталога установки Uniform Server: содержимое `practice_03/` поместите в `www\practice_03` вместе с `public/`.
2. Укажите в учебном VirtualHost **полный путь к `practice_03/public`**. Можно выбрать другую папку проекта; пример `.conf` — в `reference.pdf`.
3. Выберите вариант A/B/C в `config.php` (темы сохранены из № 2).
4. Настройте PSR-4 `App\\` → `src/` в `composer.json`; выполните `composer install`.
5. Реализуйте `Lesson::seatsLeft()`, `LessonRequest::fits()` и расчёт «осталось бы N мест» в `public/index.php`.
6. Проверьте `http://localhost:8080/` в браузере и повторите HTTP-запросы через `curl.exe`.

До выполнения этапов сообщения TODO ожидаемы. В Git включите composer.lock, но не vendor/. Замените этот README своим: вариант, версии, установка, запуск и результаты проверок.
