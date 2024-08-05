<?php

namespace App\Models;

use Core\Model;

class Migration extends Model
{
    protected static string|null $tableName = 'migrations';

    public string $name;
}
