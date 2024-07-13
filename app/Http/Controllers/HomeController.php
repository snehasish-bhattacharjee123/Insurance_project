<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class homeController extends Controller
{
    //this  function show our home page 
    public function home()
    {
        return view('front.home');
    }
}
