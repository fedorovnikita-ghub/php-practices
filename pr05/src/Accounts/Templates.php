<?php
declare(strict_types=1);
namespace Course\Accounts;

use Course\Domain\Item;
use Course\Security\Csrf;
use Course\Security\Flash;
use Course\View;

/** Готовые PHP-шаблоны. Никаких React, CDN или JavaScript для отправки форм. */
final class Templates
{
    public function __construct(private Auth $auth, private array $categories) {}
    private static function h(mixed $value): string { return View::h(is_scalar($value) ? (string) $value : ''); }
    public static function token(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::h(Csrf::token()) . '">';
    }
    public function page(string $title, string $body, bool $narrow = false): string
    {
        $user = $this->auth->current();
        $identity = $user === null ? '<a class="button secondary" href="/login">Войти</a><a class="button" href="/register">Регистрация</a>' :
            '<span class="identity">' . self::h($user->name) . ' <span class="badge">' . ($user->role === 'admin' ? 'Администратор' : 'Пользователь') . '</span></span>' .
            '<form action="/logout" method="post">' . self::token() . '<button class="secondary">Выйти</button></form>';
        $flash = Flash::take();
        $notice = $flash === null ? '' : '<div class="notice" role="status">' . self::h($flash) . '</div>';
        return '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">' .
            '<title>' . self::h($title) . ' | Каталог</title><link rel="stylesheet" href="/p05.css"></head><body>' .
            '<a class="skip" href="#content">К содержимому</a><header class="topbar"><a class="brand" href="/items"><span class="brand-mark" aria-hidden="true">К</span>Каталог ресурсов</a>' .
            '<nav aria-label="Учётная запись">' . $identity . '</nav></header><main id="content"' . ($narrow ? ' class="narrow"' : '') . '>' .
            $notice . '<div class="page-title"><h1>' . self::h($title) . '</h1></div>' . $body . '</main><footer>Веб-программирование на PHP · Практическая работа 5</footer></body></html>';
    }
    private static function field(string $key, string $label, mixed $value, array $errors, string $type = 'text', string $autocomplete = ''): string
    {
        $error = $errors[$key] ?? null;
        return '<div class="field"><label for="' . $key . '">' . $label . '</label><input id="' . $key . '" name="' . $key . '" type="' . $type . '"' .
            ($autocomplete === '' ? '' : ' autocomplete="' . $autocomplete . '"') . ' value="' . self::h($value) . '"' .
            ($error === null ? '' : ' aria-invalid="true" aria-describedby="err-' . $key . '"') . '>' .
            ($error === null ? '' : '<p class="field-error" id="err-' . $key . '">' . self::h($error) . '</p>') . '</div>';
    }
    public function account(bool $register, array $values = [], array $errors = []): string
    {
        $title = $register ? 'Создать учётную запись' : 'Вход в каталог';
        $body = '<section class="card"><p class="muted">' . ($register ? 'После регистрации можно добавлять записи в каталог.' : 'Введите email и пароль своей учётной записи.') . '</p>';
        if ($errors !== []) $body .= '<div class="error-summary" role="alert">' . self::h($errors['login'] ?? $errors['fields'] ?? 'Проверьте отмеченные поля.') . '</div>';
        $body .= '<form action="' . ($register ? '/register' : '/login') . '" method="post">' . self::token();
        if ($register) $body .= self::field('name', 'Имя', $values['name'] ?? '', $errors, 'text', 'name');
        $body .= self::field('email', 'Email', $values['email'] ?? '', $errors, 'email', 'email');
        $body .= self::field('password', 'Пароль', '', $errors, 'password', $register ? 'new-password' : 'current-password');
        if ($register) {
            $body .= '<p class="hint">От 8 символов, не более 72 байтов. Для кириллицы число байтов больше числа символов.</p>';
            $body .= self::field('confirmation', 'Повторите пароль', '', $errors, 'password', 'new-password');
        }
        $body .= '<button class="wide">' . ($register ? 'Зарегистрироваться' : 'Войти') . '</button></form>';
        $body .= '<p class="form-foot">' . ($register ? 'Уже есть учётная запись? <a href="/login">Войти</a>' : 'Нет учётной записи? <a href="/register">Зарегистрироваться</a>') . '</p></section>';
        return $this->page($title, $body, true);
    }
    public function listing(array $items, string $query): string
    {
        $user = $this->auth->current();
        $body = '<p class="muted">Найдите запись, откройте подробности или добавьте новый ресурс.</p><section class="card"><div class="toolbar">' .
            '<form class="search" action="/items" method="get"><label class="sr-only" for="q">Поиск по заголовку</label>' .
            '<input id="q" name="q" placeholder="Поиск по заголовку" value="' . self::h($query) . '"><button class="secondary">Найти</button></form>' .
            ($user === null ? '<span class="muted">Для добавления <a href="/login">войдите</a></span>' : '<a class="button" href="/items/new">Добавить запись</a>') . '</div>';
        $body .= '<div class="scroll"><table><thead><tr><th>Ресурс</th><th>Категория</th><th>Количество</th><th>Цена, мин. ед.</th><th>Запас, мин. ед.</th></tr></thead><tbody>';
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->stockValue();
            $body .= '<tr><td><a href="/items/' . $item->id . '">' . self::h($item->title) . '</a><small>ID ' . $item->id . '</small></td>' .
                '<td><span class="badge">' . self::h($item->category) . '</span></td><td>' . $item->quantity . '</td><td>' . $item->priceMinor . '</td><td>' . $item->stockValue() . '</td></tr>';
        }
        if ($items === []) $body .= '<tr><td colspan="5" class="empty">Записи не найдены. Измените поисковый запрос.</td></tr>';
        $body .= '</tbody></table></div><div class="table-foot">Записей: ' . count($items) . '<span>Стоимость запаса: ' . $sum . '</span></div></section>';
        return $this->page('Каталог ресурсов', $body);
    }
    public function detail(Item $item): string
    {
        $user = $this->auth->current();
        $body = '<a class="back" href="/items">← Каталог</a><section class="card"><dl class="details">';
        foreach (['ID' => $item->id, 'Категория' => $item->category, 'Количество' => $item->quantity, 'Цена, мин. ед.' => $item->priceMinor,
            'Стоимость запаса' => $item->stockValue(), 'Владелец, ID' => $item->ownerId ?? 'Не указан'] as $label => $value) {
            $body .= '<div><dt>' . $label . '</dt><dd>' . self::h($value) . '</dd></div>';
        }
        $body .= '</dl>';
        if ($user?->role === 'admin') {
            $body .= '<div class="actions"><a class="button secondary" href="/items/' . $item->id . '/edit">Редактировать</a>' .
                '<form action="/items/' . $item->id . '/delete" method="post">' . self::token() .
                '<label class="delete-confirm"><input type="checkbox" required> Подтверждаю удаление</label><button class="danger">Удалить запись</button></form></div>';
        }
        return $this->page($item->title, $body . '</section>');
    }
    public function itemForm(?Item $item, array $values, array $errors): string
    {
        if ($values === [] && $item !== null) $values = ['title' => $item->title, 'category' => $item->category, 'quantity' => $item->quantity, 'priceMinor' => $item->priceMinor];
        $body = '<section class="card">' . ($errors === [] ? '' : '<div class="error-summary" role="alert">Проверьте поля. ' . self::h($errors['fields'] ?? '') . '</div>') .
            '<form method="post" action="' . ($item === null ? '/items' : '/items/' . $item->id . '/edit') . '">' . self::token();
        $body .= self::field('title', 'Заголовок', $values['title'] ?? '', $errors);
        $body .= '<div class="field"><label for="category">Категория</label><select id="category" name="category"' .
            (isset($errors['category']) ? ' aria-invalid="true" aria-describedby="err-category"' : '') . '>';
        foreach ($this->categories as $category) $body .= '<option value="' . self::h($category) . '"' . (($values['category'] ?? '') === $category ? ' selected' : '') . '>' . self::h($category) . '</option>';
        $body .= '</select>' . (isset($errors['category']) ? '<p class="field-error" id="err-category">' . self::h($errors['category']) . '</p>' : '') . '</div>';
        $body .= '<div class="form-grid">' . self::field('quantity', 'Количество (0–99)', $values['quantity'] ?? '', $errors, 'number') .
            self::field('priceMinor', 'Цена, мин. ед. (0–999999)', $values['priceMinor'] ?? '', $errors, 'number') . '</div>';
        $body .= '<div class="actions"><button>Сохранить</button><a class="button secondary" href="/items">Отмена</a></div></form></section>';
        return $this->page($item === null ? 'Новая запись' : 'Редактирование записи', $body, true);
    }
}
