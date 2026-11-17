-- Вариант C: три записи. Импортируйте после schema.sql.
USE practice04_catalog;
INSERT INTO items (id, title, category, quantity, price_minor, owner_id) VALUES
    (1, 'Тетрадь', 'paper', 2, 12500, 1),
    (2, 'Линейка', 'tool', 1, 7500, 2),
    (3, 'Блокнот', 'paper', 0, 5000, 1);
