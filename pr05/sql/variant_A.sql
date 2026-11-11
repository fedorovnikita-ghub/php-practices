-- Вариант A: три записи. Импортируйте после schema.sql.
USE practice04_catalog;
INSERT INTO items (id, title, category, quantity, price_minor, owner_id) VALUES
    (1, 'USB-кабель', 'device', 2, 12500, 1),
    (2, 'Чехол', 'accessory', 1, 7500, 2),
    (3, 'Адаптер', 'device', 0, 5000, 1);
