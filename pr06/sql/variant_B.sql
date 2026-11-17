-- Вариант B: три записи. Импортируйте после schema.sql.
USE practice04_catalog;
INSERT INTO items (id, title, category, quantity, price_minor, owner_id) VALUES
    (1, 'Основы PHP', 'book', 2, 12500, 1),
    (2, 'Справочник SQL', 'manual', 1, 7500, 2),
    (3, 'Практикум Git', 'book', 0, 5000, 1);
