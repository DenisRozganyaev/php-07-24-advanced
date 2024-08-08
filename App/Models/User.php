<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static string|null $tableName = 'users';

    public string $email, $password, $create_at, $token, $token_expired_at;
}
