<?php

namespace App\Queries\Author;

use App\Models\Author;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class AuthorListQuery
{
    /**
     * Retrieve a paginated list of authors with allowed filtering and sorting.
     *
     * @return LengthAwarePaginator
     */
    public function get(): LengthAwarePaginator
    {
        return QueryBuilder::for(Author::class)
            ->allowedIncludes('books')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('last_name', 'like', "%$value%")
                            ->orWhere('first_name', 'like', "%$value%")
                            ->orWhere('patronymic', 'like', "%$value%")
                            ->orWhere('biography', 'like', "%$value%");
                    });
                })
            ])
            ->allowedSorts([
                'created_at',
                AllowedSort::callback('full_name', function ($query, $descending) {
                    $direction = $descending ? 'desc' : 'asc';
                    $query->orderBy('last_name', $direction)
                        ->orderBy('first_name', $direction)
                        ->orderBy('patronymic', $direction);
                }),
                'birth_date',
                'death_date'
            ])
            ->defaultSort('-created_at')
            ->paginate(config('pagination.authors_per_page'))
            ->withQueryString();
    }
}
