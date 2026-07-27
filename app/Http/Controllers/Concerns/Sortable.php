<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Sortable
{
    /**
     * Apply a click-to-sort column to a query, restricted to a whitelist of
     * real column names so the `sort` query param can never be used to
     * inject arbitrary SQL via the ORDER BY clause.
     */
    protected function applySort(Builder $query, Request $request, array $allowedColumns, string $defaultColumn, string $defaultDirection = 'asc'): Builder
    {
        $sort = $request->get('sort', $defaultColumn);
        $direction = $request->get('direction') === 'desc' ? 'desc' : 'asc';

        if (!in_array($sort, $allowedColumns, true)) {
            $sort = $defaultColumn;
            $direction = $defaultDirection;
        }

        return $query->orderBy($sort, $direction);
    }
}
