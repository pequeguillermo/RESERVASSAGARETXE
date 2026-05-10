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
}
