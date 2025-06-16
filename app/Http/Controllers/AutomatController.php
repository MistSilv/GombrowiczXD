<?php

namespace App\Http\Controllers;

use App\Models\Automat;
use Illuminate\Http\Request;

class AutomatController extends Controller
{
    public function index()
    {
        $automaty = Automat::orderBy('nazwa')->get();
        return view('welcome', compact('automaty'));
    }
}
