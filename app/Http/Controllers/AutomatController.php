<?php
namespace App\Http\Controllers;

use App\Models\Automat;
use Illuminate\Http\Request;

class AutomatController extends Controller
{
    public function index()
    {
        $automaty = Automat::paginate(9);
        return view('welcome', compact('automaty'));
    }

    public function create()
    {
        return view('automats.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nazwa' => 'required|string|max:255',
            'lokalizacja' => 'required|string|max:255',
        ]);

        Automat::create($validated);

        return redirect()->route('automats.create')->with('success', 'Automat dodany!');
    }

}