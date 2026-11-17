<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Domain\ItemData;

/** Дополнение к репозиторию № 4; его существующие методы сохраняются. */
final class AdminItems
{
    public function __construct(private \PDO $pdo) {}

    public function update(int $id, ItemData $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE items SET title = :title, category = :category, quantity = :quantity, price_minor = :priceMinor WHERE id = :id');
        $stmt->execute([
            'title' => $data->title,
            'category' => $data->category,
            'quantity' => $data->quantity,
            'priceMinor' => $data->priceMinor,
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}