<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManufacturerController extends Controller
{
    public function index()
    {
        $message = "Hello From Manufacturer Controller";
        return inertia('Manufacturer/Index', ['message' => $message]);
    }
}
