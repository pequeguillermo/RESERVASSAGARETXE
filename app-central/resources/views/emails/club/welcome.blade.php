<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    {!! \App\Models\Setting::where('key', 'club_email_header')->value('value') !!}
    
    <div style="padding: 20px;">
        {!! nl2br(e($bodyContent)) !!}
        
        @if(str_contains($bodyContent, '[qr]'))
            <!-- Si el body contiene el shortcode [qr], lo mostramos como imagen -->
            <div style="text-align: center; margin: 30px 0;">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($member->qr_token) }}" alt="QR Code" style="border: 2px solid #e2e8f0; border-radius: 10px; padding: 10px;">
                <p style="margin-top: 10px; font-size: 14px; color: #64748b;">Presenta este código en el establecimiento</p>
            </div>
        @endif
    </div>

    {!! \App\Models\Setting::where('key', 'club_email_footer')->value('value') !!}
</body>
</html>
