<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class DatabaseController extends Controller
{
    public function index(): Response|string
    {
        return 'hello database';
    }
}
