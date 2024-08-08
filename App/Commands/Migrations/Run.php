<?php

namespace App\Commands\Migrations;

use App\Commands\Contract\Command;
use Cli;
use Exception;
use PDOException;

class Run implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . '/database/migrations';

    public function __construct(public Cli $cli, public array $args = [])
    {
    }

    public function handle(): void
    {
        try {
            db()->beginTransaction();
            $this->cli->info("Migration process has been start...");
            // check and create 'migrations' table
            $this->createMigrationsTable();
            $this->runMigrations();
            // run migrations
            db()->commit();
            $this->cli->success("Migration process has been done!");
        } catch (PDOException $exception) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $this->cli->fatal($exception->getMessage());
        } catch (Exception $exception) {
            $this->cli->fatal($exception->getMessage());
        }
    }

    protected function runMigrations(): void
    {
        $this->cli->info("");
        $this->cli->info("Fetch migrations..");

        $migrations = scandir(static::MIGRATIONS_DIR);
        $migrations = array_values(array_diff(
            $migrations,
            ['.', '..']
        ));
        $migrations = array_values(array_diff(
            $migrations,
            $this->retrieveHandledMigrations()
        )); ;

        if (!empty($migrations)) {
            foreach ($migrations as $migration) {
                $name = preg_replace('/[\d]+_/', '', $migration);
                $this->cli->notice("- run $name");

                $script = $this->getScript($migration);

                if (empty($script)) {
                    $this->cli->fatal("An empty script!");
                    die;
                }

                $query = db()->prepare($script);

                if ($query->execute()) {
                    $this->createMigrationRecord($migration);
                    $this->cli->success("- $name migrated!");
                }
            }
        } else {
            $this->cli->info('Nothing to migrate');
        }
    }

    protected function createMigrationRecord(string $migration): void
    {
        $query = db()->prepare("INSERT INTO migrations (name) VALUES (:name)");
        $query->bindParam('name', $migration);
        $query->execute();
    }

    protected function getScript(string $migrationPath): string
    {
        $obj = null;
        $obj = require static::MIGRATIONS_DIR . '/' . $migrationPath;
        return $obj?->up() ?? '';
    }

    protected function retrieveHandledMigrations(): array
    {
        $query = db()->prepare("SELECT name FROM migrations");
        $query->execute();

        return array_map(fn ($item) => $item['name'], $query->fetchAll());
    }

    protected function createMigrationsTable(): void
    {
        $this->cli->info("- Run migration table query");
        $query = db()->prepare("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT (8) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL UNIQUE
            )
        ");

        if (!$query->execute()) {
            throw new Exception("Smth went wrong with 'migrations' table query");
        }

        $this->cli->success('Migration table was checked/created');
    }
}