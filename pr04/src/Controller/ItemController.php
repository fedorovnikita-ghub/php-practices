<?php
declare(strict_types=1);
namespace Course\Controller;

use Course\Domain\Item;
use Course\Domain\ItemData;
use Course\Http\HttpError;
use Course\Http\Request;
use Course\Http\Response;
use Course\Repository\ItemRepository;
use Course\Security\Csrf;
use Course\View;

final class ItemController
{
    public function __construct(private ItemRepository $repository, private array $categories) {}
    public function listing(Request $request): Response
    {
        $query = $request->queryText('q');
        $items = $query === '' ? $this->repository->all() : $this->repository->matching($query);
        $body = '<form action="/items" method="get"><label>Поиск по заголовку<input name="q" value="' . View::h($query) . '"></label><button>Найти</button></form>';
        $body .= '<p><a href="/items/new">Добавить запись</a></p>';
        $body .= '<div class="scroll"><table><tr><th>ID</th><th>Заголовок</th><th>Категория</th><th>Количество</th><th>Цена (мин. ед.)</th><th>Стоимость запаса</th></tr>';
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->stockValue();
            $body .= '<tr><td>' . $item->id . '</td><td><a href="/items/' . $item->id . '">' . View::h($item->title)
                . '</a></td><td>' . View::h($item->category) . '</td><td>' . $item->quantity . '</td><td>' . $item->priceMinor
                . '</td><td>' . $item->stockValue() . '</td></tr>';
        }
        $body .= '</table></div><p>Записей: ' . count($items) . '; стоимость запаса: ' . $sum . '</p>';
        return Response::html(View::page('Каталог ресурсов', $body));
    }
    public function show(int $id): Response
    {
        $item = $this->repository->find($id) ?? throw new HttpError(404, 'not_found', 'Запись не найдена');
        $body = '<p>ID ' . $item->id . '; ' . View::h($item->category) . '; количество ' . $item->quantity
            . '; цена ' . $item->priceMinor . '; владелец ' . $item->ownerId . '</p>';
        return Response::html(View::page($item->title, $body));
    }
    public function newForm(): Response
    {
        return Response::html(View::page('Новая запись', $this->form([], [])));
    }
    public function create(Request $request): Response
    {
        $input = $request->form;
        if (!Csrf::valid($input['_csrf'] ?? null)) throw new HttpError(403, 'csrf_failed', 'Некорректный CSRF-токен');
        unset($input['_csrf']);
        try {
            $data = ItemData::from($input, $this->categories, true);
        } catch (HttpError $error) {
            if ($error->status !== 422) throw $error;
            return Response::html(View::page('Исправьте поля', $this->form($input, $error->fields)), 422);
        }
        // Владелец 1: учётные записи и вход появятся в следующих работах.
        $item = $this->repository->create($data, 1);
        return Response::redirect('/items/' . $item->id);
    }
    private function form(array $values, array $errors): string
    {
        $body = '<form action="/items" method="post"><input type="hidden" name="_csrf" value="' . View::h(Csrf::token()) . '">';
        foreach (['title' => 'Заголовок (2–80 символов)', 'quantity' => 'Количество (0–99)', 'priceMinor' => 'Цена в минимальных единицах (0–999999)'] as $name => $label) {
            $value = $values[$name] ?? '';
            $body .= '<label>' . $label . '<input name="' . $name . '" value="' . View::h(is_scalar($value) ? (string) $value : '') . '"></label>';
        }
        $body .= '<label>Категория<select name="category">';
        foreach ($this->categories as $category) $body .= '<option' . (($values['category'] ?? '') === $category ? ' selected' : '') . '>' . View::h($category) . '</option>';
        $body .= '</select></label>';
        foreach ($errors as $field => $message) $body .= '<p class="error">' . View::h($field . ': ' . $message) . '</p>';
        return $body . '<button>Сохранить</button><a href="/items">Отмена</a></form>';
    }
}
