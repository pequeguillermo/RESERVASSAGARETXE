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
- Se ha modificado el listado de Excepciones para que las excepciones puntuales pasadas desaparezcan automáticamente al día siguiente, evitando que se acumulen en el panel de administrador.
- Se han ajustado los campos del formulario del **Club Sagaretxe**, eliminando DNI, Dirección y Fecha de Nacimiento, y haciendo obligatorios el resto de datos de contacto y preferencias.
- Se ha creado una **nueva sección "Clientes"**, separada de reservas y club, para registrar rápidamente contactos de forma opcional (Nombre, Teléfono, Email).
- Se ha implementado un sistema de **notificaciones por email para el Administrador**. Ahora desde la interfaz (pestaña Configuración de Correos tanto en Reservas como en Club) se puede especificar un email de destino, asunto y cuerpo (con variables) para recibir un aviso automático cada vez que haya una nueva reserva o se registre un nuevo socio.
