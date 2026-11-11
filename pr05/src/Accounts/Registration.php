<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Http\HttpError;

final class Registration
{
    /** @return array{name:string,email:string,password:string} */
    public static function validate(array $input): array
    {
        $errors = [];

        $allowedFields = ['name', 'email', 'password', 'confirmation', '_csrf'];
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $allowedFields, true)) {
                $errors[$key] = 'Недопустимое поле';
            }
        }

        $name = trim((string) ($input['name'] ?? ''));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
            $errors['name'] = 'Имя должно содержать от 2 до 80 символов';
        }

        $email = strtolower(trim((string) ($input['email'] ?? '')));
        if ($email === '' || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Укажите корректный email';
        }

        $password = (string) ($input['password'] ?? '');
        $passwordLength = mb_strlen($password);
        $passwordBytes = strlen($password);
        if ($passwordLength < 8 || $passwordBytes > 72 || str_contains($password, "\0")) {
            $errors['password'] = 'Пароль должен быть от 8 символов и не превышать 72 байта';
        }

        $confirmation = (string) ($input['confirmation'] ?? '');
        if ($password !== $confirmation) {
            $errors['confirmation'] = 'Пароли не совпадают';
        }

        if (!empty($errors)) {
            throw new HttpError(422, 'validation_failed', 'Ошибка валидации формы', $errors);
        }

        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ];
    }
}