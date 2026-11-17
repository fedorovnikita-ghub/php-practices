# Практическая работа № 6: JSON API и JWT-авторизация

**Студент:** Фёдоров Никита, группа CS-304(с)  
**Вариант:** A (техника)

## Среда

| Компонент | Версия или путь |
|---|---|
| Uniform Server | 15.0.1 |
| PHP (Apache, из `/health.php`) | 8.3.0 |
| PHP CLI (`php -v`) | 8.3.0 |
| Composer (`composer --version`) | 2.6.5 |
| Библиотека JWT | `firebase/php-jwt` (v7.2.1) |
| Сервер БД (`SELECT VERSION();` в phpMyAdmin) | 8.4.3 |
| Папка проекта | `C:\WebLabs\pr06` |
| Веб-корень Apache | `C:\WebLabs\pr06\public` |

## Запуск

1. Создание отдельной базы данных для 6-й практики (экспорт/импорт данных из предыдущей работы) и обновление параметров подключения в `config.local.php`.
2. Установка зависимостей через Composer с учётом особенностей окружения (`composer install --prefer-source`).
3. Генерация 32-байтного секретного ключа для JWT с помощью скрипта `php bin/p06-key.php` в защищённую директорию `var/jwt.key`.
4. Настройка правил перенаправления (`RewriteRule`) в `public/.htaccess` для обработки запросов `/api/...` через `api.php`.
5. Добавление директивы `CGIPassAuth On` в конфигурационный блок Apache для передачи заголовка `Authorization` (Bearer Token) в PHP.

## Примеры запросов к API (`curl` в PowerShell)

### 1. Получение JWT-токена (вход под пользователем или администратором)

```powershell
# Вход обычного пользователя (права на создание записей)
curl.exe -i -X POST http://localhost:8088/api/login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"user@example.test\",\"password\":\"12345678\"}'

# Вход администратора (права на создание, редактирование и удаление)
curl.exe -i -X POST http://localhost:8088/api/login `
  -H "Content-Type: application/json" `
  -d '{\"email\":\"admin@example.test\",\"password\":\"12345678\"}'
```

### 2. Просмотр списка товаров и конкретной записи (публично)

```powershell
# Весь каталог
curl.exe http://localhost:8088/api/items

# Поиск по названию
curl.exe "http://localhost:8088/api/items?q=USB"

# Конкретная запись по ID
curl.exe http://localhost:8088/api/items/1
```

### 3. Создание новой записи (требуется токен пользователя или администратора)

```powershell
@'
{"title":"Монитор Dell","category":"device","quantity":5,"priceMinor":45000}
'@ | curl.exe -i -X POST http://localhost:8088/api/items `
  -H "Content-Type: application/json" `
  -H "Authorization: Bearer <JWT_TOKEN>" `
  --data-binary "@-"
```

### 4. Редактирование записи по ID (требуется токен администратора)

```powershell
@'
{"title":"Монитор Dell 27","category":"device","quantity":3,"priceMinor":52000}
'@ | curl.exe -i -X PUT http://localhost:8088/api/items/1 `
  -H "Content-Type: application/json" `
  -H "Authorization: Bearer <ADMIN_JWT_TOKEN>" `
  --data-binary "@-"
```

### 5. Удаление записи по ID (требуется токен администратора)

```powershell
curl.exe -i -X DELETE http://localhost:8088/api/items/1 `
  -H "Authorization: Bearer <ADMIN_JWT_TOKEN>"
```