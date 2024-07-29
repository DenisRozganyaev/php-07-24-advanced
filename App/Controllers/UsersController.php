<?php
namespace App\Controllers;

use App\Enums\Http\Status;
use Core\Controller;

class UsersController extends Controller
{
    public function edit(int $id, int $note_id)
    {
        return $this->response(Status::OK, compact('id', 'note_id'));
    }

    public function store()
    {

    }
}