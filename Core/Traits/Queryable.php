<?php

namespace Core\Traits;

// User::all();
// User::create();
// User::where()->
// User::select()->... :static = (User::class)
// User::select()->... :self = (Model::class)
// $user = 1 row
use App\Enums\SQL;
use PDO;
use splitbrain\phpcli\Exception;

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

    static public function __callStatic(string $name, array $arguments)
    {
        if (in_array($name, ['where'])) {
            return call_user_func_array([new static, $name], $arguments);
        }

        throw new Exception('Method not allowed', 422);
    }

    public function __call(string $name, array $arguments)
    {
        if (in_array($name, ['where'])) {
            return call_user_func_array([$this, $name], $arguments);
        }

        throw new Exception('Method not allowed', 422);
    }

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
     * @return bool
     */
    static public function create(array $fields): bool
    {
        $params = static::prepareCreateParams($fields);
        $query = db()->prepare('INSERT INTO ' . static::$tableName . " ($params[keys]) VALUES ($params[placeholders]);");

        return $query->execute($fields);
    }

    /**
     * @param array $fields = ['email' => '', 'password' => ...]
     * @return static|null
     */
    static public function createAndReturn(array $fields): null|static
    {
        static::create($fields);
        return static::find(db()->lastInsertId());
    }

    static public function all(): array
    {
        return static::select()->get();
    }

    static public function delete(int $id): bool
    {
        $query = db()->prepare("DELETE FROM " . static::$tableName . " WHERE id = :id");
        $query->bindParam('id', $id);

        return $query->execute();
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

    public function toSql(): string
    {
        return static::$query;
    }

    public function and(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->required(['where'], 'AND can not be used without');

        static::$query .= ' AND';
        $this->commands[] = 'and';

        return $this->where($column, $operator, $value);
    }

    public function or(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->required(['where'], 'AND can not be used without');

        static::$query .= ' OR';
        $this->commands[] = 'or';

        return $this->where($column, $operator, $value);
    }

    public function join(string $table, array $conditions, string $type = 'LEFT'): static
    {
        $this->required(['select'], 'JOIN can not be used without');

        $this->commands[] = 'join';

        static::$query .= " $type JOIN $table ON ";

        $lastKey = array_key_last($conditions);
        foreach($conditions as $key => $arr) {
            static::$query .= "$arr[left] $arr[operator] $arr[right]" . ($key !== $lastKey) ? " AND " : '';
        }

        return $this;
    }

    public function pluck(string $column): array
    {
        $result = $this->get();
        return !empty($result) ? array_map(fn($obj) => $obj->$column, $result) : [];
    }

    // User::where()
    protected function where(string $column, SQL $operator = SQL::EQUAL, mixed $value = null): static
    {
        $this->prevent(['order', 'limit', 'having', 'group'], 'WHERE can not be used after');
        $obj = in_array('select', $this->commands) ? $this : static::select();

        $value = $this->transformWhereValue($value);

        if (!in_array('where', $obj->commands)) {
            static::$query .= ' WHERE';
            $obj->commands[] = 'where';
        }

        static::$query .= " $column $operator->value $value";

        return $obj;
    }

    protected function prevent(array $preventMethods, string $message = ''): void
    {
        foreach ($preventMethods as $method) {
            if (in_array($method, $this->commands)) {
                $message = sprintf(
                    '%s: %s [%s]',
                    static::class,
                    $message,
                    $method
                );
                throw new Exception($message, 422);
            }
        }
    }

    protected function required(array $requiredMethods, string $message = ''): void
    {
        foreach ($requiredMethods as $method) {
            if (!in_array($method, $this->commands)) {
                $message = sprintf(
                    '%s: %s [%s]',
                    static::class,
                    $message,
                    $method
                );
                throw new Exception($message, 422);
            }
        }
    }

    protected function transformWhereValue(mixed $value): string|int|float
    {
        $checkOnString = fn($v) => !is_null($v) && !is_bool($v) && !is_numeric($v) && !is_array($v) && $v !== SQL::NULL->value;

        if ($checkOnString($value)) {
            $value = "'$value'";
        }

        if (is_null($value)) {
            $value = SQL::NULL->value; // NULL
        }

        if (is_array($value)) {
            $value = array_map(fn ($item) => $checkOnString($item) ? "'$item'" : $item, $value);
            $value = '(' . implode(', ', $value) . ')';
        }

        if (is_bool($value)) {
            $value = $value ? 'TRUE' : 'FALSE';
        }

        return $value;
    }
}
