<?php
declare(strict_types=1);

/** Готовая веб-валидация: те же поля и ограничения, что в работе № 2. */
function validateLessonForm(array $input, array $topics): array
{
    $values = [];
    $errors = [];
    foreach (['fullName', 'email', 'topic', 'seats', 'agree'] as $name) {
        $raw = $input[$name] ?? '';
        if (!is_string($raw) || preg_match('//u', $raw) !== 1) {
            $values[$name] = '';
            $errors[$name] = 'Ожидается текст в UTF-8.';
        } else {
            $values[$name] = trim($raw);
        }
    }
    if (!isset($errors['fullName']) && (mb_strlen($values['fullName']) < 2 || mb_strlen($values['fullName']) > 60)) {
        $errors['fullName'] = 'Имя: от 2 до 60 символов.';
    }
    if (!isset($errors['email']) && (strlen($values['email']) > 120 || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false)) {
        $errors['email'] = 'Укажите корректный email (не более 120 байт).';
    }
    if (!isset($errors['topic']) && !in_array($values['topic'], $topics, true)) {
        $errors['topic'] = 'Выберите тему своего варианта.';
    }
    if (!isset($errors['seats']) && preg_match('/^[1-5]$/D', $values['seats']) !== 1) {
        $errors['seats'] = 'Места: ровно одна цифра от 1 до 5.';
    }
    if (!isset($errors['agree']) && $values['agree'] !== '1') {
        $errors['agree'] = 'Подтвердите согласие.';
    }
    return ['values' => $values, 'errors' => $errors];
}

function html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
