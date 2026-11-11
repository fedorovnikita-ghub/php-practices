-- Практическая работа № 4: база и таблица каталога. Импортируйте первым.
CREATE DATABASE IF NOT EXISTS practice04_catalog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practice04_catalog;
CREATE TABLE items (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(80) NOT NULL,
    category VARCHAR(20) NOT NULL,
    quantity TINYINT UNSIGNED NOT NULL,
    price_minor INT UNSIGNED NOT NULL,
    owner_id INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT items_title_length CHECK (CHAR_LENGTH(TRIM(title)) BETWEEN 2 AND 80),
    CONSTRAINT items_category CHECK (category IN ('device', 'accessory', 'book', 'manual', 'paper', 'tool')),
    CONSTRAINT items_quantity CHECK (quantity <= 99),
    CONSTRAINT items_price CHECK (price_minor <= 999999)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
