<?php
declare(strict_types=1);

namespace Course\Repository;

use Course\Domain\Item;
use Course\Domain\ItemData;

final class PdoItemRepository implements ItemRepository
{
    public function __construct(private \PDO $pdo) {}

    private function item(array $row): Item
    {
        $ownerId = $row['owner_id'] === null ? null : (int) $row['owner_id'];
        return new Item((int) $row['id'], $row['title'], $row['category'], (int) $row['quantity'],
            (int) $row['price_minor'], $ownerId);
    }

    public function all(): array
    {
        $sql = 'SELECT id, title, category, quantity, price_minor, owner_id FROM items ORDER BY id';
        return array_map($this->item(...), $this->pdo->query($sql)->fetchAll());
    }

    public function find(int $id): ?Item
    {
        $stmt = $this->pdo->prepare('SELECT id, title, category, quantity, price_minor, owner_id FROM items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->item($row) : null;
    }

    public function matching(string $query): array
    {
        $escaped = addcslashes($query, '%_\\');
        $stmt = $this->pdo->prepare('SELECT id, title, category, quantity, price_minor, owner_id FROM items WHERE title LIKE :query ORDER BY id');
        $stmt->execute(['query' => '%' . $escaped . '%']);
        return array_map($this->item(...), $stmt->fetchAll());
    }

    public function create(ItemData $data, int $ownerId): Item
    {
        $stmt = $this->pdo->prepare('INSERT INTO items (title, category, quantity, price_minor, owner_id) VALUES (:title, :category, :quantity, :priceMinor, :ownerId)');
        $stmt->execute([
            'title' => $data->title,
            'category' => $data->category,
            'quantity' => $data->quantity,
            'priceMinor' => $data->priceMinor,
            'ownerId' => $ownerId,
        ]);
        return $this->find((int) $this->pdo->lastInsertId());
    }
}