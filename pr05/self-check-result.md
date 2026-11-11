## Самопроверка

Вариант A, адрес http://localhost:8088, 2026-11-02 07:27, PHP CLI 8.3.0. Пройдено 23 из 23.

| Запрос | Ожидается | Получено | Совпало |
|---|---|---|---|
| GET /health.php | 200, pdo_mysql и config_local равны true | 200 | да |
| GET /items | 200, записи USB-кабель, Чехол, Адаптер | 200 | да |
| GET /items/1 | 200, USB-кабель | 200 | да |
| GET /items/999 | 404 | 404 | да |
| GET /items/0 | 404 | 404 | да |
| GET /items/01 | 404 | 404 | да |
| GET /items/abc | 404 | 404 | да |
| GET /items-extra | 404 | 404 | да |
| GET /items?q=usb | 200, только USB-кабель | 200 | да |
| GET /items?q=% | 200, Записей: 0 | 200 | да |
| GET /items?q[]=x | 400 | 400 | да |
| PUT /items | 405, Allow: GET, HEAD, POST | 405, Allow: GET, HEAD, POST | да |
| POST /items/1 | 405, Allow: GET, HEAD | 405, Allow: GET, HEAD | да |
| POST /missing | 404 | 404 | да |
| HEAD /items | 200 без тела | 200 | да |
| X-Request-Id у 200 и 404 | есть у обоих, значения различаются | 404 | да |
| GET /composer.json | 404 | 404 | да |
| GET /config.local.php | 404 | 404 | да |
| GET /items/new | 200, форма с CSRF-токеном | 200 | да |
| POST /items без токена | 403 | 403 | да |
| POST /items, заголовок «x» | 422 | 422 | да |
| POST /items | 303, Location: /items/<новый id> | 303, Location: /items/7 | да |
| GET /items/7 | 200, новая запись, кавычки экранированы | 200 | да |
