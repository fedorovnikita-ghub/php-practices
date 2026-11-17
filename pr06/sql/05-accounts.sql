-- Выполнить ОДИН РАЗ в выбранной базе завершённой практической 04.
-- Перед запуском сохранить резервную копию БД. USE намеренно не задан.
-- Сначала проверить отсутствие users и columns.owner_id.is_nullable = NO.
CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    email VARCHAR(254) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Старые 1 и 2 были условными владельцами, а не учётными записями.
ALTER TABLE items MODIFY owner_id INT UNSIGNED NULL DEFAULT NULL;
UPDATE items SET owner_id = NULL;
ALTER TABLE items ADD CONSTRAINT items_owner_fk FOREIGN KEY (owner_id) REFERENCES users(id);
