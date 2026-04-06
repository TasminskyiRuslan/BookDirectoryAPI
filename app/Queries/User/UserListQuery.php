<?php

namespace App\Queries\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserListQuery
{
    /**
     * Retrieve a paginated list of users with filters and sorting.
     *
     * @return LengthAwarePaginator
     */
    public function get(): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('name', 'like', "%$value%")
                            ->orWhere('email', 'like', "%$value%");
                    });
                }),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->whereHas('roles', function ($q) use ($value) {
                        $q->where('name', $value);
                    });
                }),
            ])
                ->allowedSorts([
                    'created_at',
                    'name',
                ])
                ->defaultSort('-created_at')
                ->paginate(config('pagination.user_per_page'));
    }
}
