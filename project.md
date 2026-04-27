# Reservas Sagaretxe

## Interacciones

### 2026-04-27
- **Ajustes de Emails:** Se crearon las vistas para configurar las plantillas de email (Asunto y cuerpo).
- **Flujo de correos:** 
  - Al realizar reserva se envía correo con enlaces para confirmar y cancelar.
  - Cronjob configurado para enviar recordatorios a falta de 12 horas y 6 horas.
  - Cronjob configurado para pedir reseñas 24 horas después de la cita.
- **Nuevos estados:** Se añadieron los estados `confirmada_cliente` y `cancelada_cliente` que se aplican automáticamente cuando el usuario interactúa con los links del correo, reflejándose visualmente (verde/rojo) en la lista de reservas.
- **Plano de sala:** Se ha modificado el sistema de mesas Drag&Drop (Sala) para permitir crear mesas redondas, modificando el CSS, JS y el guardado en base de datos. Se ha reemplazado el sistema de ventanas emergentes nativas (prompt/confirm) por un modal HTML personalizado para introducir nombre, capacidad y forma. También se ha incorporado la capacidad de hacer doble clic sobre una mesa existente para editar sus propiedades o eliminarla (incluso desasignándola de reservas existentes si se borra).
- **Servidor Local:** Proyecto disponible en `http://localhost/RESERVASSAGARETXE` (puerto 80 por defecto en XAMPP).
