<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index()
    {
        return Inertia::render('Club', [
            'members' => Member::withCount('reservations')->orderBy('created_at', 'desc')->get(),
            'settings' => \App\Models\Setting::all()->pluck('value', 'key')
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',
            'pref_space' => 'required|string|max:255',
            'pref_food' => 'required|string|max:255',
            'pref_drink1' => 'required|string|max:255',
            'pref_drink2' => 'required|string|max:255',
            'pref_time' => 'required|string|max:255',
            'how_knew_us' => 'nullable|string|max:255',
            'active' => 'boolean'
        ]);

        $validated['qr_token'] = \Illuminate\Support\Str::random(10);
        
        Member::create($validated);
        
        return redirect()->back()->with('success', 'Miembro creado correctamente');
    }
}
