<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function message(){
        $msg = 'こんにちは';
        return $msg;
    }
}