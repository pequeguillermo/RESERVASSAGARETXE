<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Validation;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'method' => 'required|string|in:qr,phone',
        ]);

        $validation = Validation::create([
            'member_id' => $request->member_id,
            'method' => $request->method,
        ]);

        return response()->json([
            'message' => 'Validación registrada correctamente',
            'validation_id' => $validation->id
        ]);
    }
}
