<?php
namespace App\Http\Controllers;

use App\Models\Automat;

class AutomatController extends Controller
{
    public function index()
    {
        $automaty = Automat::all();
        return view('welcome', compact('automaty'));
    }
}