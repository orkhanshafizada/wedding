<?php

namespace Modules\Form\Repositories;

use Modules\Form\Models\FormResponse;
use Modules\Menu\Models\Menu;

class FormResponseRepository
{
    public function findForMenuOrFail(
        Menu $menu,
        FormResponse $response,
        bool $lockForUpdate = false
    ): FormResponse {
        $query = FormResponse::query()
            ->whereKey($response->getKey())
            ->where('menu_id', $menu->getKey());

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->firstOrFail();
    }

    public function updateStatus(FormResponse $response, int $status): FormResponse
    {
        $response->status = $status;
        $response->saveOrFail();

        return $response->refresh();
    }
}