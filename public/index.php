<?php

use App\Enums\Http\Status;
use Core\Router;
use Dotenv\Dotenv;

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $dotenv = Dotenv::createUnsafeImmutable(BASE_DIR);
    $dotenv->load();

    require_once BASE_DIR . '/routes/api.php';
    die(Router::dispatch($_SERVER['REQUEST_URI']));
} catch (PDOException $exception) {
    die(
        jsonResponse(
            Status::INTERNAL_SERVER_ERROR,
            [
                'errors' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            ]
        )
    );
} catch (Throwable $exception) {
    die(
        jsonResponse(
            $exception->getCode() === 0 ? Status::UNPROCESSABLE_ENTITY : Status::from($exception->getCode()),
            [
                'errors' => [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace()
                ]
            ]
        )
    );
}
