# Proyecto Reservas Sagaretxe

**Servicios locales:**
- Laravel API / Backend: `c:\xampp\php\php.exe artisan serve --port=8080` (IMPORTANTE: Verificar SIEMPRE el estado con command_status)
- Vite (Frontend assets): `npm run dev` activo en segundo plano (IMPORTANTE: Verificar SIEMPRE el estado con command_status)

**Resumen de la última iteración:**
- Se ha implementado la creación manual de reservas desde el administrador, incluyendo el desglose de adultos/niños y los metadatos especiales (alergias, celíacos, movilidad reducida, etc.).
- Se ha implementado la gestión y creación manual de miembros del Club Sagaretxe usando el mismo modal de edición/creación.
- Se ha configurado el sistema de roles (`superadmin` y `admin`), protegiendo las pestañas de Configuración de Correo y Usuarios para que solo el Superadmin tenga acceso.
- Se ha creado la pestaña de "Usuarios" (superadmin) para dar de alta/baja a los administradores del restaurante con sus códigos de acceso.
- Se ha implementado el límite de comensales (`max_diners`). Al "Cerrar Hoy/Mañana" o al crear una excepción, ahora el sistema te pregunta si quieres dejar un máximo de comensales abierto. Si ese límite se alcanza, la API ya no aceptará más reservas.
- Se ha optimizado la gestión de **Excepciones y Fechas Especiales**: ahora el sistema elimina físicamente de la base de datos todas las excepciones puntuales pasadas automáticamente al acceder al panel (ajustado al huso horario `Europe/Madrid`), evitando que se acumulen infinitamente.
- Se han ajustado los campos del formulario del **Club Sagaretxe**, eliminando DNI, Dirección y Fecha de Nacimiento, y haciendo obligatorios el resto de datos de contacto y preferencias.
- Se ha creado una **nueva sección "Clientes"**, separada de reservas y club, para registrar rápidamente contactos de forma opcional (Nombre, Teléfono, Email).
- Se ha implementado un sistema de **notificaciones por email para el Administrador**. Ahora desde la interfaz (pestaña Configuración de Correos tanto en Reservas como en Club) se puede especificar un email de destino, asunto y cuerpo (con variables) para recibir un aviso automático cada vez que haya una nueva reserva o se registre un nuevo socio.
- Se ha modificado el sistema del **Club Sagaretxe** para que el ID de socio se muestre como un "Número de Socio", empezando por el 9000 (ID interno + 9000).
- Se ha actualizado el panel de **validación manual** en el Club para permitir la búsqueda tanto por Teléfono como por el nuevo Número de Socio (ej. introduciendo 9005).
- Se ha implementado el envío del **Email de Bienvenida** a los nuevos socios al registrarse, con el código QR y variables incluyendo `[numero_socio]`.
