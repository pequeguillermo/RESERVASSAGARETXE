<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $member = Member::where('phone', $request->phone)->first();

        if (!$member) {
            $member = Member::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'qr_token' => Str::uuid()->toString(),
                'active' => true,
            ]);
        }

        return response()->json([
            'message' => 'Miembro procesado correctamente',
            'member' => $member
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $member = Member::where('phone', $request->phone)->first();

        if ($member) {
            return response()->json([
                'exists' => true,
                'active' => (bool)$member->active,
                'member_id' => $member->id,
                'name' => $member->name
            ]);
        }

        return response()->json([
            'exists' => false,
            'active' => false,
            'member_id' => null
        ]);
    }

    public function validateQr(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $member = Member::where('qr_token', $request->token)->first();

        if ($member && $member->active) {
            return response()->json([
                'valid' => true,
                'member' => $member
            ]);
        }

        return response()->json([
            'valid' => false,
            'member' => $member // return member to show name even if inactive, if needed
        ]);
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'surname' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'pref_space' => 'nullable|string|max:50',
            'pref_food' => 'nullable|string|max:50',
            'pref_drink1' => 'nullable|string|max:50',
            'pref_drink2' => 'nullable|string|max:50',
            'pref_time' => 'nullable|string|max:50',
            'how_knew_us' => 'nullable|string|max:50',
            'active' => 'boolean',
        ]);

        $member->update($validated);

        return redirect()->back();
    }
}
