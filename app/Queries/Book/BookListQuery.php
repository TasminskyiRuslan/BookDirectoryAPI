<?php

namespace App\Queries\Book;

use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class BookListQuery
{
    /**
     * Retrieve a paginated list of books with allowed filtering and sorting.
     *
     * @return LengthAwarePaginator
     */
    public function get(): LengthAwarePaginator
    {
        return QueryBuilder::for(Book::class)
            ->allowedIncludes('authors')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('title', 'like', "%$value%")
                            ->orWhere('description', 'like', "%$value%")
                            ->orWhereHas('authors', function ($q) use ($value) {
                                $q->where('last_name', 'like', "%$value%")
                                    ->orWhere('first_name', 'like', "%$value%")
                                    ->orWhere('patronymic', 'like', "%$value%");
                            });
                    });
                }),
                AllowedFilter::callback('author', function ($query, $value) {
                    $query->whereHas('authors', function ($q) use ($value) {
                        $q->where('slug', $value);
                    });
                })
            ])
            ->allowedSorts([
                'created_at',
                'title',
                'publication_date'
            ])
            ->defaultSort('-created_at')
            ->paginate(config('pagination.books_per_page'))
            ->withQueryString();
    }
}
