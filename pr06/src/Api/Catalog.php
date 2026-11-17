<?php
declare(strict_types=1);

namespace Course\Api;

use Course\Accounts\AdminItems;
use Course\Domain\{Item, ItemData};
use Course\Http\{Request, Response, HttpError};
use Course\Repository\ItemRepository;

final class Catalog
{
    public function __construct(
        private ItemRepository $items,
        private AdminItems $adminItems,
        private Identity $identity,
        private array $categories
    ) {}

    private function data(Item $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'category' => $item->category,
            'quantity' => $item->quantity,
            'priceMinor' => $item->priceMinor,
            'ownerId' => $item->ownerId,
        ];
    }

    private function existing(int $id): Item
    {
        return $this->items->find($id) ?? throw new HttpError(404, 'not_found', 'Запись не найдена');
    }

    public function listing(Request $request): Response
    {
        $q = $request->query['q'] ?? null;
        if ($q !== null && !is_string($q)) {
            throw new HttpError(400, 'invalid_query', 'Параметр q должен быть строкой');
        }

        $search = is_string($q) ? trim($q) : '';
        $list = $search === '' ? $this->items->all() : $this->items->matching($search);

        return Json::response([
            'items' => array_map($this->data(...), $list),
        ]);
    }

    public function show(int $id): Response
    {
        return Json::response([
            'item' => $this->data($this->existing($id)),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $this->identity->require($request);
        $data = ItemData::from(Json::object($request), $this->categories);
        $item = $this->items->create($data, $user->id);

        return Json::response(
            ['item' => $this->data($item)],
            201,
            ['Location' => '/api/items/' . $item->id]
        );
    }

    public function update(Request $request, int $id): Response
    {
        $this->identity->require($request, true);
        $this->existing($id);
        $data = ItemData::from(Json::object($request), $this->categories);
        $this->adminItems->update($id, $data);

        return Json::response([
            'item' => $this->data($this->existing($id)),
        ]);
    }

    public function delete(Request $request, int $id): Response
    {
        $this->identity->require($request, true);
        $this->existing($id);
        $this->adminItems->delete($id);

        return new Response(204, '');
    }
}