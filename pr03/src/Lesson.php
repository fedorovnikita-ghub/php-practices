<?php
declare(strict_types=1);

namespace App;

final readonly class Lesson
{
    public function __construct(
        public string $topic,
        public string $title,
        public string $date,
        public int $capacity,
        public int $occupied,
    ) {
        if ($capacity < 0 || $occupied < 0 || $occupied > $capacity) {
            throw new \InvalidArgumentException('Некорректное число мест');
        }
    }

    public function seatsLeft(): int
    {
        return $this->capacity - $this->occupied;
    }
}
