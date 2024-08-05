<?php

use App\Enums\Http\Status;
use Core\Router;
use Dotenv\Dotenv;

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $dotenv = Dotenv::createUnsafeImmutable(BASE_DIR);
    $dotenv->load();

    dd(\App\Models\User::create([
        'email' => 'test@mail.com',
        'password' => 'test1234'
    ]));

//    require_once BASE_DIR . '/routes/api.php';
//    die(Router::dispatch($_SERVER['REQUEST_URI']));
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
