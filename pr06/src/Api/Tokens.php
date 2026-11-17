<?php
declare(strict_types=1);
namespace Course\Api;
use Firebase\JWT\{JWT, Key};
use Course\Http\HttpError;
final class Tokens
{
    private const ISSUER = 'urn:course:catalog';
    private const AUDIENCE = 'catalog-api';
    public function __construct(private string $key)
    {
        if (strlen($key) < 32) throw new \RuntimeException('JWT key must contain at least 32 random bytes');
    }
    public function issue(int $userId): string
    {
        $now = time();
        return JWT::encode(['iss'=>self::ISSUER,'aud'=>self::AUDIENCE,'sub'=>(string)$userId,
            'iat'=>$now,'exp'=>$now+900], $this->key, 'HS256');
    }
    public function subject(string $token): int
    {
        try {
            $claims = JWT::decode($token, new Key($this->key, 'HS256'));
            $now = time();
            if (($claims->iss ?? null) !== self::ISSUER || ($claims->aud ?? null) !== self::AUDIENCE
                || !is_int($claims->iat ?? null) || !is_int($claims->exp ?? null)
                || $claims->iat > $now || $claims->exp <= $now || $claims->exp <= $claims->iat
                || !is_string($claims->sub ?? null) || preg_match('/^[1-9][0-9]{0,8}$/D', $claims->sub) !== 1) {
                throw new \UnexpectedValueException('Invalid JWT profile');
            }
            return (int)$claims->sub;
        } catch (\UnexpectedValueException | \DomainException $error) {
            throw new HttpError(401, 'invalid_token', 'Токен недействителен или просрочен', [], ['WWW-Authenticate'=>'Bearer error="invalid_token"']);
        }
    }
}
