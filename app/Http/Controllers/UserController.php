<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request){
        return Inertia::render('Auth.Register');
    }
}
