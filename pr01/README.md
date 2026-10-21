# Практическая работа 01. Вариант A

* **Версия PHP:** 8.3.0
* **Адрес запуска:** http://localhost:8088/

## Результаты проверок через curl.exe

### 1. Успешный GET-запрос (/quote) с количествами 2 и 1
```bash
curl.exe -i "http://localhost:8088/quote?qty_first=2&qty_second=1"
# Ответ сервера: HTTP/1.1 200 OK
# Сумма: 325.00; минимальные единицы: 32500
# Присутствует заголовок X-Practice: 01
```

### 2. Успешный POST-запрос (/preview) с количествами 2 и 1
```bash
curl.exe -i -d "qty_first=2&qty_second=1" "http://localhost:8088/preview"
# Ответ сервера: HTTP/1.1 200 OK
# Сумма: 325.00; минимальные единицы: 32500
```

### 3. Запрос с неправильным методом (GET к /preview)
```bash
curl.exe -i "http://localhost:8088/preview"
# Ответ сервера: HTTP/1.1 405 Method Not Allowed
# Присутствует заголовок Allow: POST
```
