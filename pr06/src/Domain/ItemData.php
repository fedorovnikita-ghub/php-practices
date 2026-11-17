<?php
declare(strict_types=1);
namespace Course\Domain;

use Course\Http\HttpError;

final readonly class ItemData
{
    private function __construct(public string $title, public string $category, public int $quantity, public int $priceMinor) {}

    public static function from(array $input, array $categories, bool $form = false): self
    {
        $errors = [];
        $allowed = ['title', 'category', 'quantity', 'priceMinor'];
        if (array_diff(array_keys($input), $allowed) !== []) $errors['fields'] = 'Неизвестные поля';
        $title = $input['title'] ?? null;
        if (!is_string($title) || preg_match('//u', $title) !== 1) {
            $errors['title'] = 'Ожидается строка UTF-8';
            $title = '';
        }
        $title = trim($title);
        if (mb_strlen($title) < 2 || mb_strlen($title) > 80) $errors['title'] = 'Заголовок: 2–80 символов';
        $category = $input['category'] ?? null;
        if (!is_string($category) || !in_array($category, $categories, true)) $errors['category'] = 'Категория из списка';
        $numbers = [];
        foreach (['quantity' => 99, 'priceMinor' => 999999] as $field => $max) {
            $raw = $input[$field] ?? null;
            if ($form && is_string($raw) && preg_match('/^(0|[1-9][0-9]{0,5})$/D', $raw) === 1) $raw = (int) $raw;
            if (!is_int($raw) || $raw < 0 || $raw > $max) $errors[$field] = "Ожидается целое 0–$max";
            $numbers[$field] = $raw;
        }
        if ($errors !== []) throw new HttpError(422, 'validation_error', 'Проверьте поля', $errors);
        return new self($title, $category, $numbers['quantity'], $numbers['priceMinor']);
    }
}
