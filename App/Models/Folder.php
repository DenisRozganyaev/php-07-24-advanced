<?php

namespace App\Models;

use Core\Model;

class Folder extends Model
{
    protected static string|null $tableName = 'folders';

    public string $title, $created_at, $updated_at;
    public int $user_id;
}
