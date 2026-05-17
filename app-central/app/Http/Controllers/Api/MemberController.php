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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'surname' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'pref_space' => 'nullable|string|max:50',
            'pref_food' => 'nullable|string|max:50',
            'pref_drink1' => 'nullable|string|max:50',
            'pref_drink2' => 'nullable|string|max:50',
            'pref_time' => 'nullable|string|max:50',
            'how_knew_us' => 'nullable|string|max:50',
        ]);

        $member = Member::where('phone', $request->phone)->first();
        $isNew = false;

        if (!$member) {
            $validated['qr_token'] = Str::uuid()->toString();
            $validated['active'] = true;
            $member = Member::create($validated);
            $isNew = true;
        } else {
            // Actualizar si ya existía para guardar los campos nuevos
            $member->update($validated);
        }

        // Notificación al administrador (solo para miembros nuevos)
        if ($isNew) {
            // Notificación al socio
            $memberEmail = $member->email;
            if ($memberEmail) {
                try {
                    $subjectTpl = \App\Models\Setting::where('key', 'club_subject_welcome')->value('value') ?? '¡Bienvenido al Club Sagaretxe!';
                    $bodyTpl = \App\Models\Setting::where('key', 'club_email_welcome')->value('value')
                        ?? "Hola [nombre],\n¡Gracias por unirte al Club Sagaretxe!";

                    $body = str_replace(
                        ['[nombre]', '[apellidos]', '[telefono]', '[email]', '[cp]', '[numero_socio]', '[qr]'],
                        [$member->name, $member->surname ?? '', $member->phone, $member->email ?? '-', $member->postal_code ?? '-', $member->id + 9000, ''],
                        $bodyTpl
                    );
                    $subject = str_replace(
                        ['[nombre]', '[apellidos]', '[numero_socio]'],
                        [$member->name, $member->surname ?? '', $member->id + 9000],
                        $subjectTpl
                    );

                    \Illuminate\Support\Facades\Mail::to($memberEmail)->send(new \App\Mail\MemberWelcome($subject, $body, $member));
                } catch (\Exception $e) {
                    \Log::error('Error sending club welcome notification: ' . $e->getMessage());
                }
            }

            $adminEmail = \App\Models\Setting::where('key', 'club_admin_email')->value('value');
            if ($adminEmail) {
                try {
                    $subjectTpl = \App\Models\Setting::where('key', 'club_subject_admin')->value('value') ?? '🎉 Nuevo Miembro del Club';
                    $bodyTpl = \App\Models\Setting::where('key', 'club_email_admin')->value('value')
                        ?? "Se ha registrado un nuevo miembro en el Club Sagaretxe:\n\nNombre: [nombre] [apellidos]\nTeléfono: [telefono]\nEmail: [email]\nCódigo Postal: [cp]";

                    $body = str_replace(
                        ['[nombre]', '[apellidos]', '[telefono]', '[email]', '[cp]'],
                        [$member->name, $member->surname ?? '', $member->phone, $member->email ?? '-', $member->postal_code ?? '-'],
                        $bodyTpl
                    );
                    $subject = str_replace(
                        ['[nombre]', '[apellidos]'],
                        [$member->name, $member->surname ?? ''],
                        $subjectTpl
                    );

                    \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminNotification($subject, $body));
                } catch (\Exception $e) {
                    \Log::error('Error sending club admin notification: ' . $e->getMessage());
                }
            }
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

        $search = $request->phone;
        $memberId = is_numeric($search) ? ((int)$search - 9000) : null;
        
        $member = Member::where('phone', $search)
            ->when($memberId !== null && $memberId > 0, function($query) use ($memberId) {
                return $query->orWhere('id', $memberId);
            })
            ->first();

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
