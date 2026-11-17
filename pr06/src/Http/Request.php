<?php
declare(strict_types=1);
namespace Course\Http;

final readonly class Request
{
    public array $headers;
    public function __construct(public string $method, public string $path, public array $query = [],
        public array $form = [], array $headers = [], public string $body = '')
    {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }
    public static function fromGlobals(): self
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) $headers[str_replace('_', '-', substr($key, 5))] = $value;
        }
        $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'] ?? '';
        $body = file_get_contents('php://input', false, null, 0, 1048577);
        if ($body === false) throw new HttpError(400, 'read_error', 'Не удалось прочитать тело');
        if (strlen($body) > 1048576) throw new HttpError(413, 'too_large', 'Тело запроса превышает 1 МиБ');
        return new self($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/',
            $_GET, $_POST, $headers, $body);
    }
    public function header(string $name): ?string { return $this->headers[strtolower($name)] ?? null; }
    public function queryText(string $name): string
    {
        $value = $this->query[$name] ?? '';
        if (!is_string($value) || preg_match('//u', $value) !== 1 || mb_strlen($value) > 80) {
            throw new HttpError(400, 'invalid_query', 'Параметр поиска должен быть строкой до 80 символов');
        }
        return trim($value);
    }
}
