<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Reserva</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #2b6cb0; text-align: center;">¡Reserva Confirmada!</h2>
        <p>Hola <strong>{{ $reservation->name }}</strong>,</p>
        <p>Tu reserva en <strong>Sagaretxe Club</strong> ha sido procesada correctamente. A continuación, te mostramos los detalles:</p>
        
        <ul style="list-style: none; padding: 0; background: #f7fafc; padding: 15px; border-radius: 5px;">
            <li><strong>Fecha y Hora:</strong> {{ $reservation->date->format('d/m/Y H:i') }}</li>
            <li><strong>Personas:</strong> {{ $reservation->people }}</li>
            <li><strong>Teléfono:</strong> {{ $reservation->phone }}</li>
            @if($reservation->discount_applied)
            <li><strong>Descuento de Miembro:</strong> <span style="color: #38a169;">¡Aplicado! 🎉</span></li>
            @endif
        </ul>

        @if($reservation->notes)
        <p><strong>Tus notas:</strong> {{ $reservation->notes }}</p>
        @endif

        <p style="text-align: center; margin-top: 30px;">¡Te esperamos!</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #999; text-align: center;">Sagaretxe Club - Todos los derechos reservados.</p>
    </div>
</body>
</html>
