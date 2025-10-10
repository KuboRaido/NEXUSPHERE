<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pcr;



class PostController extends Controller
{
    public function post(){
        return view('index');
    }
    public function create(){
        return view('create');
    }
}