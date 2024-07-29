<?php

use App\Enums\Http\Status;
use Core\Router;

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

try {
    require_once BASE_DIR . '/routes/api.php';
    die(Router::dispatch($_SERVER['REQUEST_URI']));
} catch (Throwable $exception) {
    die(
        jsonResponse(
            Status::from($exception->getCode()),
            [
                'errors' => [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            ]
        )
    );
}
