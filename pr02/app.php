<?php
declare(strict_types=1);

function topics(): array
{
    return match ((require __DIR__ . '/config.php')['variant']) {
        'A' => ['php' => 'PHP', 'http' => 'HTTP'],
        'B' => ['git' => 'Git', 'testing' => 'Тестирование'],
        'C' => ['sql' => 'SQL', 'design' => 'Проектирование'],
        default => throw new InvalidArgumentException('Вариант A/B/C'),
    };
}

function textField(array $input, string $name, array &$errors): string
{
    $value = $input[$name] ?? '';

    if (!is_string($value)) {
        $errors[$name] = 'Передано недопустимое значение.';
        return '';
    }

    if (preg_match('//u', $value) !== 1) {
        $errors[$name] = 'Текст содержит некорректные символы UTF-8.';
        return '';
    }

    return trim($value);
}

function validateRegistration(array $input): array
{
    $values = [];
    $errors = [];

    $values['fullName'] = textField($input, 'fullName', $errors);
    $values['email']    = textField($input, 'email', $errors);
    $values['topic']    = textField($input, 'topic', $errors);
    $values['seats']    = textField($input, 'seats', $errors);
    $values['agree']    = textField($input, 'agree', $errors);

    if (!isset($errors['fullName'])) {
        $len = mb_strlen($values['fullName'], 'UTF-8');
        if ($len < 2 || $len > 60) {
            $errors['fullName'] = 'Имя должно содержать от 2 до 60 символов.';
        }
    }

    if (!isset($errors['email'])) {
        if (strlen($values['email']) > 120) {
            $errors['email'] = 'Email слишком длинный (максимум 120 байт).';
        } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Неверный формат email.';
        }
    }

    if (!isset($errors['topic'])) {
        $allowedTopics = array_keys(topics());
        if (!in_array($values['topic'], $allowedTopics, true)) {
            $errors['topic'] = 'Выбрана недопустимая тема.';
        }
    }

    if (!isset($errors['seats'])) {
        if (preg_match('/^[1-5]$/', $values['seats']) !== 1) {
            $errors['seats'] = 'Количество мест должно быть одной цифрой от 1 до 5.';
        }
    }

    if (!isset($errors['agree'])) {
        if ($values['agree'] !== '1') {
            $errors['agree'] = 'Необходимо согласие с условиями.';
        }
    }

    return [
        'values' => $values,
        'errors' => $errors,
    ];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function form(array $values, array $errors): string
{
    $body = '<form action="/register" method="post"><p>Предпросмотр заявки; сохранения пока нет.</p>';
    foreach (['fullName' => 'Имя', 'email' => 'Email', 'seats' => 'Количество мест (1–5)'] as $name => $label) {
        $body .= '<label for="' . $name . '">' . $label . '</label><input id="' . $name . '" name="' . $name . '" value="' . h($values[$name] ?? '') . '">';
        if (isset($errors[$name])) $body .= '<p class="error">' . h($errors[$name]) . '</p>';
    }
    $body .= '<label for="topic">Тема</label><select id="topic" name="topic"><option value="">Выберите</option>';
    foreach (topics() as $key => $label) {
        $body .= '<option value="' . h($key) . '"' . (($values['topic'] ?? '') === $key ? ' selected' : '') . '>' . h($label) . '</option>';
    }
    $body .= '</select>';
    if (isset($errors['topic'])) $body .= '<p class="error">' . h($errors['topic']) . '</p>';
    $body .= '<label><input type="checkbox" name="agree" value="1"' . (($values['agree'] ?? '') === '1' ? ' checked' : '') . '> Согласен с обработкой учебной заявки</label>';
    if (isset($errors['agree'])) $body .= '<p class="error">' . h($errors['agree']) . '</p>';
    return $body . '<button>Проверить заявку</button></form>';
}
