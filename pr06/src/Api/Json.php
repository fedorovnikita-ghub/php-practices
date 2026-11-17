<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Http\{Request, Response, HttpError};
use JsonException;
use stdClass;

final class Json
{
    public static function object(Request $request): array
    {
        $contentType = $request->header('Content-Type') ?? '';
        $mediaType = strtolower(trim(explode(';', $contentType, 2)[0]));

        if ($mediaType !== 'application/json') {
            throw new HttpError(415, 'unsupported_media_type', 'Ожидается Content-Type: application/json');
        }

        try {
            $value = json_decode($request->body, false, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new HttpError(400, 'invalid_json', 'Некорректный JSON');
        }

        if (!$value instanceof stdClass) {
            throw new HttpError(400, 'object_required', 'Ожидается JSON-объект');
        }

        return (array) $value;
    }

    public static function response(mixed $data, int $status = 200, array $headers = []): Response
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new Response(
            $status,
            $body,
            ['Content-Type' => 'application/json; charset=UTF-8'] + $headers
        );
    }

    public static function problem(HttpError $error, string $requestId): Response
    {
        $body = json_encode([
            'type' => 'about:blank',
            'title' => $error->getMessage(),
            'status' => $error->status,
            'code' => $error->errorCode,
            'errors' => (object) $error->fields,
            'requestId' => $requestId
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new Response(
            $error->status,
            $body,
            ['Content-Type' => 'application/problem+json; charset=UTF-8'] + $error->headers
        );
    }
}