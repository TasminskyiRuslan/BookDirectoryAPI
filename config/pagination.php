<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Users Per Page
    |--------------------------------------------------------------------------
    |
    | This value determines how many user records will be displayed on
    | a single page of results. This is utilized by the UserListQuery
    | to manage API pagination limits.
    |
    */

    'users_per_page' => env('USERS_PER_PAGE', 30),

    /*
    |--------------------------------------------------------------------------
    | Authors Per Page
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of authors to be shown per page.
    | Lower values can improve performance when the application
    | handles a large volume of author data.
    |
    */

    'authors_per_page' => env('AUTHORS_PER_PAGE', 20),

    /*
    |--------------------------------------------------------------------------
    | Books Per Page
    |--------------------------------------------------------------------------
    |
    | This value controls the pagination limit for book listings.
    | Setting this value in the environment file allows you to
    | tune the UI experience without modifying the source code.
    |
    */

    'books_per_page' => env('BOOKS_PER_PAGE', 15),
];
