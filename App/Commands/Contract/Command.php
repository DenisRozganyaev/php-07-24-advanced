<?php

namespace App\Commands\Contract;

use Cli;

interface Command
{
    public function __construct(Cli $cli, array $args = []);

    public function handle(): void;
}
