<?php

declare(strict_types=1);

namespace Backend\Models;

use Backend\Database\Connection;

final class Category
{
    public static function all(): array
    {
        return Connection::get()
            ->query('SELECT id, name, image_url FROM categories ORDER BY name')
            ->fetchAll();
    }
}
