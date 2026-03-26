<?php

namespace App\Enums;

enum UserPermission: string
{
    // Author
    case AUTHOR_INDEX   = 'author.index';
    case AUTHOR_STORE   = 'author.store';
    case AUTHOR_SHOW    = 'author.show';
    case AUTHOR_UPDATE  = 'author.update';
    case AUTHOR_DESTROY = 'author.destroy';

    // Book
    case BOOK_INDEX     = 'book.index';
    case BOOK_STORE     = 'book.store';
    case BOOK_SHOW      = 'book.show';
    case BOOK_UPDATE    = 'book.update';
    case BOOK_DESTROY   = 'book.destroy';

    // User
    case USER_INDEX     = 'user.index';
    case USER_SHOW      = 'user.show';
    case USER_DESTROY   = 'user.destroy';
    case USER_ROLE_UPDATE = 'user.role.update';
}
