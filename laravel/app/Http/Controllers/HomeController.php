<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Mon Application',
            'message' => 'Bienvenue sur notre plateforme!'
        ];
        
        return view('welcome', $data);
    }
}
