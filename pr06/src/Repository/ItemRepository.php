<?php
declare(strict_types=1);
namespace Course\Repository;

use Course\Domain\Item;
use Course\Domain\ItemData;

interface ItemRepository
{
    /** @return list<Item> */
    public function all(): array;
    public function find(int $id): ?Item;
    /** @return list<Item> */
    public function matching(string $query): array;
    public function create(ItemData $data, int $ownerId): Item;
}
