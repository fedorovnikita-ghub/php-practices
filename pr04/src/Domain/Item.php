<?php
declare(strict_types=1);
namespace Course\Domain;

final readonly class Item
{
    public function __construct(
        public int $id,
        public string $title,
        public string $category,
        public int $quantity,
        public int $priceMinor,
        public int $ownerId,
    ) {
        if ($id < 1 || $ownerId < 1 || $quantity < 0 || $quantity > 99 || $priceMinor < 0 || $priceMinor > 999999) {
            throw new \InvalidArgumentException('Недопустимые числовые поля Item');
        }
    }

    public function stockValue(): int
    {
        return $this->quantity * $this->priceMinor;
    }
}
