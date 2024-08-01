<?php

const BASE_DIR = __DIR__;
require_once BASE_DIR . '/vendor/autoload.php';

use App\Commands\Contract\Command;
use Dotenv\Dotenv;
use splitbrain\phpcli\CLI as BaseCli;
use splitbrain\phpcli\Options;

class Cli extends BaseCli
{
    const COMMANDS_PATH = BASE_DIR . '/config/commands.php';

    protected array $commands = [], $setup = [];

    public function __construct($autocatch = true)
    {
        $config = require_once static::COMMANDS_PATH;
        $this->setup = $config['setup'];
        $this->commands = $config['commands'];

        parent::__construct($autocatch);
    }

    protected function setup(Options $options)
    {
        $dotenv = Dotenv::createUnsafeImmutable(BASE_DIR);
        $dotenv->load();

        for($i = 0; $i < count($this->setup); $i++) {
            $options->registerCommand($this->setup[$i]['command'], $this->setup[$i]['description'] ?? '');
            if (!empty($this->setup[$i]['arguments'])) {
                foreach ($this->setup[$i]['arguments'] as $arg) {
                    $options->registerArgument(
                        $arg['name'],
                        $arg['description'] ?? '',
                        $arg['required'] ?? false,
                        $this->setup[$i]['command']
                    );
                }
            }
        }
    }

    protected function main(Options $options)
    {
        if (array_key_exists($options->getCmd(), $this->commands)) {
            $cmd = new $this->commands[$options->getCmd()]($this, $options->getArgs());
            if ($cmd instanceof Command) {
                call_user_func([$cmd, 'handle']);
            }
        } else {
            if (!empty($options->getCmd())) {
                $this->warning('Command not found');
            }
            echo $options->help();
        }
    }
}

$cli = new Cli();
$cli->run();
