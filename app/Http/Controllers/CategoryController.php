<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $message = "Hello From Category Controller";
        return inertia('Category/Index', ['message' => $message]);
    }
}
