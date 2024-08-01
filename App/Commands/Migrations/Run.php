<?php

namespace App\Commands\Migrations;

use App\Commands\Contract\Command;
use Cli;
use Exception;
use PDOException;

class Run implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . '/migrations';

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