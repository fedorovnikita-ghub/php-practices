<?php
declare(strict_types=1);

$project = dirname(__DIR__);
header('Content-Type: text/html; charset=UTF-8');
$panel = '';
$lessonsHtml = '';
$formHtml = '';
try {
    if (!is_file($project . '/bootstrap.php')) {
        throw new RuntimeException('Проект не найден: проверьте содержимое practice_03 и настройку веб-корня на public.');
    }
    $app = require $project . '/bootstrap.php';
    require_once $project . '/form.php';
    $lessons = $app['lessons'];
    $topics = array_map(static fn(\App\Lesson $lesson): string => $lesson->topic, $lessons);
    $values = [];
    $errors = [];
    foreach ($lessons as $lesson) {
        $free = $lesson->seatsLeft();
        $lessonsHtml .= '<tr><td>' . html($lesson->title) . '</td><td>' . html($lesson->topic)
            . '</td><td>' . html($lesson->date) . '</td><td>' . $free . '</td></tr>';
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = validateLessonForm($_POST, $topics);
        $values = $result['values'];
        $errors = $result['errors'];
        if ($errors !== []) {
            http_response_code(422);
            $panel = '<section class="feedback error" aria-live="polite"><h2>Проверьте поля формы</h2><p>Заявка не прошла проверку.</p></section>';
        } else {
            $request = new \App\LessonRequest($values['fullName'], $values['email'], $values['topic'], (int) $values['seats']);
            foreach ($lessons as $lesson) {
                if ($lesson->topic !== $request->topic) {
                    continue;
                }
                if ($request->fits($lesson)) {
                    $remaining = $lesson->seatsLeft() - $request->seats;
                    $panel = '<section class="feedback" aria-live="polite"><h2>Предпросмотр заявки</h2>'
                        . '<p>Имя: ' . html($request->fullName) . '</p><p>Email: ' . html($request->email)
                        . '</p><p>Занятие: ' . html($lesson->title) . '</p><p>Мест: ' . $request->seats
                        . '</p><p>После такой заявки осталось бы ' . $remaining . ' мест.</p>'
                        . '<strong>Проверка пройдена. Это предпросмотр: запись пока не создана.</strong></section>';
                } else {
                    http_response_code(422);
                    $panel = '<section class="feedback error" aria-live="polite"><h2>Недостаточно свободных мест</h2>'
                        . '<p>' . html($lesson->title) . ': доступно ' . $lesson->seatsLeft()
                        . ', запрошено ' . $request->seats . '.</p><p>Заявка не сохранена.</p></section>';
                }
                break;
            }
        }
    } elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        header('Allow: GET, POST');
        $panel = '<p class="error">Разрешены GET и POST.</p>';
    }
    foreach (['fullName' => 'Имя', 'email' => 'Email', 'seats' => 'Количество мест (1–5)'] as $name => $label) {
        $formHtml .= '<label for="' . $name . '">' . $label . '</label><input id="' . $name . '" name="' . $name . '" value="'
            . html($values[$name] ?? '') . '">';
        if (isset($errors[$name])) {
            $formHtml .= '<p class="error">' . html($errors[$name]) . '</p>';
        }
    }
    $formHtml .= '<label for="topic">Тема</label><select id="topic" name="topic"><option value="">Выберите</option>';
    foreach ($lessons as $lesson) {
        $formHtml .= '<option value="' . html($lesson->topic) . '"'
            . (($values['topic'] ?? '') === $lesson->topic ? ' selected' : '') . '>' . html($lesson->title) . '</option>';
    }
    $formHtml .= '</select>';
    if (isset($errors['topic'])) {
        $formHtml .= '<p class="error">' . html($errors['topic']) . '</p>';
    }
    $formHtml .= '<label class="check"><input type="checkbox" name="agree" value="1"'
        . (($values['agree'] ?? '') === '1' ? ' checked' : '') . '> Согласен с обработкой учебной заявки</label>';
    if (isset($errors['agree'])) {
        $formHtml .= '<p class="error">' . html($errors['agree']) . '</p>';
    }
    $variant = html($app['variant']);
} catch (RuntimeException $error) {
    http_response_code(501);
    $panel = '<section class="feedback error"><p>' . htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p></section>';
    $variant = '—';
} catch (Throwable $error) {
    http_response_code(500);
    error_log((string) $error);
    $panel = '<section class="feedback error"><p>Ошибка приложения. Проверьте журнал PHP.</p></section>';
    $variant = '—';
}
?><!doctype html>
<html lang="ru"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Учебные занятия · Практическая работа 03</title><link rel="stylesheet" href="style.css"></head>
<body><main><p class="variant">Вариант <?= $variant ?></p>
<h1>Учебные занятия и заявки</h1><p>Посмотрите, сколько мест осталось, и проверьте заявку на нужное занятие.</p>
<?= $panel ?>
<section><h2>Занятия</h2><table><thead><tr><th>Занятие</th><th>Тема</th><th>Дата</th><th>Свободно</th></tr></thead>
<tbody><?= $lessonsHtml ?></tbody></table></section>
<section><h2>Проверить заявку</h2><form method="post" action=""><p>Выберите занятие и укажите 1–5 мест.</p>
<?= $formHtml ?><button type="submit">Проверить заявку</button></form></section>
</main></body></html>
