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
        
        $member = Member::create($validated);
        
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

        // Notificación al administrador
        $adminEmail = \App\Models\Setting::where('key', 'club_admin_email')->value('value');
        if ($adminEmail) {
            try {
                $subjectTpl = \App\Models\Setting::where('key', 'club_subject_admin')->value('value') ?? '🎉 Nuevo Miembro del Club';
                $bodyTpl = \App\Models\Setting::where('key', 'club_email_admin')->value('value')
                    ?? "Se ha registrado un nuevo miembro en el Club Sagaretxe:\n\nNombre: [nombre] [apellidos]\nTeléfono: [telefono]\nEmail: [email]\nCódigo Postal: [cp]";

                $body = str_replace(
                    ['[nombre]', '[apellidos]', '[telefono]', '[email]', '[cp]', '[numero_socio]'],
                    [$member->name, $member->surname ?? '', $member->phone, $member->email ?? '-', $member->postal_code ?? '-', $member->id + 9000],
                    $bodyTpl
                );
                $subject = str_replace(
                    ['[nombre]', '[apellidos]', '[numero_socio]'],
                    [$member->name, $member->surname ?? '', $member->id + 9000],
                    $subjectTpl
                );

                \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AdminNotification($subject, $body));
            } catch (\Exception $e) {
                \Log::error('Error sending club admin notification: ' . $e->getMessage());
            }
        }
        
        return redirect()->back()->with('success', 'Miembro creado correctamente');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->back()->with('success', 'Miembro eliminado correctamente');
    }
}
