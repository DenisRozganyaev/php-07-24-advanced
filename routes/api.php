<?php

use App\Controllers\UsersController;
use Core\Router;

// _/users/4/edit
// _/users/14/edit
// _/users/114/edit
Router::get('users/{id:\d+}/edit'); //->controller(UsersController::class)->action('edit');
