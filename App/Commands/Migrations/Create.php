<?php

namespace App\Commands\Migrations;

use App\Commands\Contract\Command;
use Cli;
use Core\Contract\Migration;

class Create implements Command
{
    const MIGRATIONS_DIR = BASE_DIR . '/database/migrations';
    public function __construct(public Cli $cli, public array $args = [])
    {
    }

    public function handle(): void
    {
        $this->createDir();
        $this->createMigration();
    }

    protected function createMigration(): void
    {
        $name = time() . '_' . $this->args[0];
        $fullPath = static::MIGRATIONS_DIR . "/$name.php";

        try {
            file_put_contents($fullPath, Migration::TEMPLATE, FILE_APPEND);
            $this->cli->info("Migration '$name' was created");
        } catch (\Exception $exception) {
            $this->cli->error($exception->getMessage());
        }
    }

    protected function createDir(): void
    {
        if (!file_exists(static::MIGRATIONS_DIR)) {
            mkdir(static::MIGRATIONS_DIR);
            $this->cli->info("Migrations directory was created.");
        }
    }
}