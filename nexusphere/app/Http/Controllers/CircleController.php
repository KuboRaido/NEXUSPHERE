<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Model\Circle;

class CircleController extends Controller
{
    public function circleFront()
    {
            return view('circle');
    }
    public function circleCreate(Request $request)
    {
        
            return view('circleCreate');
        
    }
}