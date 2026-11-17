<?php
// Самопроверка практической № 4. Запуск из корня проекта при работающем Apache и MySQL:
//   php bin/self-check.php                  (адрес по умолчанию http://localhost:8080)
//   php bin/self-check.php http://localhost:8081
// Скрипт отправляет настоящие HTTP-запросы и записывает полученные ответы в self-check-result.md.
// Проверка создания добавляет в таблицу одну запись «Самопроверка …».
declare(strict_types=1);

$root = dirname(__DIR__);
$base = rtrim($argv[1] ?? 'http://localhost:8080', '/');
$config = require $root . '/config.php';
$letter = $config['variant'] ?? '?';
$variant = (require $root . '/data/variants.php')[$letter] ?? null;
if ($variant === null) {
    fwrite(STDERR, "В config.php укажите вариант A, B или C.\n");
    exit(2);
}
$search = ['A' => 'usb', 'B' => 'php', 'C' => 'блокнот'][$letter];
[$first, $second, $third] = $variant['titles'];
$cookie = '';
$results = [];
$rows = [];

function request(string $method, string $path, ?array $form = null): array
{
    global $base, $cookie;
    $headers = $cookie === '' ? [] : ['Cookie: ' . $cookie];
    $options = ['method' => $method, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 10];
    if ($form !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $options['content'] = http_build_query($form);
    }
    $options['header'] = implode("\r\n", $headers);
    $body = @file_get_contents($base . $path, false, stream_context_create(['http' => $options]));
    if (!isset($http_response_header[0])) {
        return ['status' => 0, 'headers' => [], 'body' => ''];
    }
    $list = [];
    foreach (array_slice($http_response_header, 1) as $line) {
        if (!str_contains($line, ':')) continue;
        [$name, $value] = array_map('trim', explode(':', $line, 2));
        $list[strtolower($name)] = $value;
        if (strtolower($name) === 'set-cookie') $cookie = explode(';', $value)[0];
    }
    return ['status' => (int) explode(' ', $http_response_header[0])[1], 'headers' => $list, 'body' => (string) $body];
}

// Подсказка по ответу: незавершённый этап, отсутствие сервера или ошибка настройки.
function reason(array $r): string
{
    if ($r['status'] === 0) return 'сервер не отвечает: запущен ли Apache, верен ли адрес';
    if (preg_match('/TODO (P04-[A-Z]+)/', $r['body'], $m) === 1) return 'осталась заглушка ' . $m[1];
    if ($r['status'] === 503) return trim(html_entity_decode(strip_tags(preg_replace('/.*<p>|<\/p>.*/s', '', $r['body']))));
    return 'получено ' . $r['status'];
}

function check(string $request, string $expected, array $r, bool $ok, string $hint): void
{
    global $results, $rows;
    $results[] = $ok;
    $actual = $r['status'] === 0 ? 'нет ответа' : (string) $r['status'];
    if (isset($r['headers']['allow'])) $actual .= ', Allow: ' . $r['headers']['allow'];
    if (isset($r['headers']['location'])) $actual .= ', Location: ' . $r['headers']['location'];
    $rows[] = [$request, $expected, $actual, $ok ? 'да' : 'нет'];
    echo ($ok ? '[OK] ' : '[!!] ') . $request . ' -> ' . $actual . ($ok ? '' : "\n     Ожидается: $expected. " . ($hint !== '' ? $hint : reason($r))) . "\n";
}

$h = request('GET', '/health.php');
$health = json_decode($h['body'], true) ?: [];
check('GET /health.php', '200, pdo_mysql и config_local равны true', $h,
    $h['status'] === 200 && ($health['extensions']['pdo_mysql'] ?? false) && ($health['config_local'] ?? false),
    $h['status'] === 200 ? 'Включите pdo_mysql в php.ini Apache и создайте config.local.php' : reason($h));
if ($h['status'] === 0) {
    echo "\nСервер недоступен, дальнейшие проверки пропущены.\n";
    exit(1);
}

$r = request('GET', '/items');
check('GET /items', "200, записи $first, $second, $third", $r,
    $r['status'] === 200 && str_contains($r['body'], $first) && str_contains($r['body'], $third), '');
$r = request('GET', '/items/1');
check('GET /items/1', "200, $first", $r, $r['status'] === 200 && str_contains($r['body'], $first), '');
foreach (['/items/999', '/items/0', '/items/01', '/items/abc', '/items-extra'] as $path) {
    $r = request('GET', $path);
    check("GET $path", '404', $r, $r['status'] === 404, $r['status'] === 200 ? 'Router: проверяйте весь путь и шаблон id' : '');
}
$r = request('GET', '/items?q=' . rawurlencode($search));
check("GET /items?q=$search", "200, только $first", $r,
    $r['status'] === 200 && str_contains($r['body'], $first) && !str_contains($r['body'], $second), '');
$r = request('GET', '/items?q=%25');
check('GET /items?q=%', '200, Записей: 0', $r, $r['status'] === 200 && str_contains($r['body'], 'Записей: 0'),
    $r['status'] === 200 ? 'matching(): экранируйте % и _ через addcslashes()' : '');
$r = request('GET', '/items?q%5B%5D=x');
check('GET /items?q[]=x', '400', $r, $r['status'] === 400, '');

$r = request('PUT', '/items');
$allow = array_map('trim', explode(',', $r['headers']['allow'] ?? ''));
sort($allow);
check('PUT /items', '405, Allow: GET, HEAD, POST', $r, $r['status'] === 405 && $allow === ['GET', 'HEAD', 'POST'],
    $r['status'] === 405 ? 'Router: соберите методы всех маршрутов с совпавшим путём' : '');
$r = request('POST', '/items/1');
check('POST /items/1', '405, Allow: GET, HEAD', $r,
    $r['status'] === 405 && !str_contains($r['headers']['allow'] ?? 'POST', 'POST'), '');
$r = request('POST', '/missing');
check('POST /missing', '404', $r, $r['status'] === 404, $r['status'] === 405 ? 'Router: 405 только для найденного пути' : '');
$r = request('HEAD', '/items');
check('HEAD /items', '200 без тела', $r, $r['status'] === 200 && $r['body'] === '',
    $r['status'] === 200 ? 'Response::send(): не выводите тело для HEAD' : '');
$one = request('GET', '/items');
$two = request('GET', '/missing');
$ids = [$one['headers']['x-request-id'] ?? '', $two['headers']['x-request-id'] ?? ''];
check('X-Request-Id у 200 и 404', 'есть у обоих, значения различаются', $two,
    $ids[0] !== '' && $ids[1] !== '' && $ids[0] !== $ids[1], 'Application: добавьте заголовок после try/catch');
foreach (['/composer.json', '/config.local.php'] as $path) {
    $r = request('GET', $path);
    check("GET $path", '404', $r, $r['status'] === 404, 'Веб-корень должен указывать на public/');
}

$form = request('GET', '/items/new');
preg_match('/name="_csrf" value="([0-9a-f]+)"/', $form['body'], $m);
check('GET /items/new', '200, форма с CSRF-токеном', $form, $form['status'] === 200 && isset($m[1]), '');
$stamp = date('H:i:s');
$item = ['title' => 'Самопроверка "' . $stamp . '"', 'category' => $variant['categories'][0], 'quantity' => '1', 'priceMinor' => '100'];
$r = request('POST', '/items', $item);
check('POST /items без токена', '403', $r, $r['status'] === 403, '');
$r = request('POST', '/items', ['_csrf' => $m[1] ?? '', 'title' => 'x'] + $item);
check('POST /items, заголовок «x»', '422', $r, $r['status'] === 422, '');
$r = request('POST', '/items', ['_csrf' => $m[1] ?? ''] + $item);
$location = $r['headers']['location'] ?? '';
check('POST /items', '303, Location: /items/<новый id>', $r,
    $r['status'] === 303 && preg_match('#^/items/[1-9][0-9]*$#', $location) === 1, '');
if ($location !== '') {
    $r = request('GET', $location);
    check("GET $location", '200, новая запись, кавычки экранированы', $r,
        $r['status'] === 200 && str_contains($r['body'], '&quot;' . $stamp . '&quot;'), '');
}

$passed = count(array_filter($results));
$total = count($results);
$lines = ['## Самопроверка', '', 'Вариант ' . $letter . ', адрес ' . $base . ', ' . date('Y-m-d H:i') . ', PHP CLI ' . PHP_VERSION
    . '. Пройдено ' . $passed . ' из ' . $total . '.', '', '| Запрос | Ожидается | Получено | Совпало |', '|---|---|---|---|'];
foreach ($rows as $row) $lines[] = '| ' . implode(' | ', array_map(static fn(string $c): string => str_replace('|', '\\|', $c), $row)) . ' |';
file_put_contents($root . '/self-check-result.md', implode("\n", $lines) . "\n");
echo "\nПройдено $passed из $total. Таблица ответов: self-check-result.md\n";
exit($passed === $total ? 0 : 1);
