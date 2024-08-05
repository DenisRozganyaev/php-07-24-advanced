<?php

namespace Core\Traits;

// User::all();
// User::create();
// User::where()->
// User::select()->... :static = (User::class)
// User::select()->... :self = (Model::class)
// $user = 1 row
use PDO;

trait Queryable
{
    static protected string|null $tableName = null;
    static protected string $query = '';

    /*
     * SELECT
     * FROM
     * [JOIN]
     * [WHERE]
     * [GROUP BY]
     * [HAVING] => WHERE for GROUP BY
     * [ORDER BY]
     */
    protected array $commands = [];

    /*
     * $columns = ['id', 'name']
     *
     * implode => id, name => string
     */
    static public function select(array $columns = ['*']): static
    {
        static::resetQuery();
        static::$query = 'SELECT ' . implode(', ', $columns) . ' FROM ' . static::$tableName;

        $obj = new static;
        $obj->commands[] = 'select';

        return $obj;
    }

    static public function find(int $id): static|false
    {
        $query = db()->prepare('SELECT * FROM ' . static::$tableName . ' WHERE id = :id');
        $query->bindParam('id', $id);
        $query->execute();

        return $query->fetchObject(static::class);
    }

    static public function findBy(string $column, mixed $value): static|false
    {
        $query = db()->prepare('SELECT * FROM ' . static::$tableName . " WHERE $column = :$column");
        $query->bindParam($column, $value);
        $query->execute();

        return $query->fetchObject(static::class);
    }

    /**
     * @param array $fields = ['email' => '', 'password' => ...]
     * @return static|null
     */
    static public function create(array $fields): null|static
    {
        $params = static::prepareCreateParams($fields);
        $query = db()->prepare('INSERT INTO ' . static::$tableName . " ($params[keys]) VALUES ($params[placeholders]);");

        if (!$query->execute($fields)) {
            return null;
        }

        return static::find(db()->lastInsertId());
    }

    static public function all(): array
    {
        return static::select()->get();
    }

    static protected function prepareCreateParams(array $fields): array
    {
        $keys = array_keys($fields);
        $placeholders = preg_filter('/^/', ':', $keys);

        return [
            'keys' => implode(', ', $keys),
            'placeholders' => implode(', ', $placeholders)
        ];
    }


    static protected function resetQuery(): void
    {
        static::$query = '';
    }

    public function get(): array
    {
        return db()->query(static::$query)->fetchAll(PDO::FETCH_CLASS, static::class);
    }
}
