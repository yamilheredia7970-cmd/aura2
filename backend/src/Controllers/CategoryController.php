<?php

declare(strict_types=1);

namespace Backend\Controllers;

use Backend\Models\Category;
use Backend\Support\Response;

final class CategoryController
{
    public static function index(): void
    {
        Response::json(['categories' => Category::all()]);
    }
}
