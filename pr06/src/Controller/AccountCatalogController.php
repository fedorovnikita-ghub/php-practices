<?php
declare(strict_types=1);
namespace Course\Controller;

use Course\Accounts\Auth;
use Course\Accounts\AdminItems;
use Course\Accounts\Templates;
use Course\Domain\ItemData;
use Course\Http\HttpError;
use Course\Http\Request;
use Course\Http\Response;
use Course\Repository\ItemRepository;
use Course\Security\Csrf;
use Course\Security\Flash;

final class AccountCatalogController
{
    public function __construct(private ItemRepository $items, private AdminItems $admin,
        private Auth $auth, private Templates $ui, private array $categories) {}
    public function listing(Request $r): Response
    {
        $query = $r->queryText('q');
        return Response::html($this->ui->listing($query === '' ? $this->items->all() : $this->items->matching($query), $query));
    }
    public function show(int $id): Response
    {
        return Response::html($this->ui->detail($this->items->find($id) ?? throw new HttpError(404, 'not_found', 'Запись не найдена')));
    }
    public function newForm(): Response
    {
        $user = $this->auth->guard();
        if ($user instanceof Response) return $user;
        return Response::html($this->ui->itemForm(null, [], []));
    }
    public function editForm(int $id): Response
    {
        $user = $this->auth->guard(true);
        if ($user instanceof Response) return $user;
        $item = $this->items->find($id) ?? throw new HttpError(404, 'not_found', 'Запись не найдена');
        return Response::html($this->ui->itemForm($item, [], []));
    }
    public function save(Request $r, ?int $id = null): Response
    {
        $user = $id === null ? $this->auth->guard() : $this->auth->guard(true);
        if ($user instanceof Response) return $user;

        $input = $this->input($r);
        $item = $id === null ? null : ($this->items->find($id) ?? throw new HttpError(404, 'not_found', 'Запись не найдена'));
        try { $data = ItemData::from($input, $this->categories, true); }
        catch (HttpError $e) {
            if ($e->status !== 422) throw $e;
            return Response::html($this->ui->itemForm($item, $input, $e->fields), 422);
        }
        if ($id === null) $id = $this->items->create($data, $user->id)->id;
        else $this->admin->update($id, $data);
        Flash::put($item === null ? 'Запись добавлена.' : 'Изменения сохранены.');
        return Response::redirect('/items/' . $id);
    }
    public function delete(Request $r, int $id): Response
    {
        $user = $this->auth->guard(true);
        if ($user instanceof Response) return $user;

        $this->input($r);
        if ($this->items->find($id) === null) throw new HttpError(404, 'not_found', 'Запись не найдена');
        $this->admin->delete($id);
        Flash::put('Запись удалена.');
        return Response::redirect('/items');
    }
    private function input(Request $r): array
    {
        $input = $r->form;
        if (!Csrf::valid($input['_csrf'] ?? null)) throw new HttpError(403, 'csrf_failed', 'Некорректный CSRF-токен');
        unset($input['_csrf']);
        return $input;
    }
}
