<?php

namespace App\Helper\Enums\Post;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}
