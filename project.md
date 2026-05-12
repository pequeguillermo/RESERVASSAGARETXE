# Reservas Sagaretxe

## Interacciones

### 2026-05-12
- **Levantamiento de servicios:**
  - App Central Backend (API): Levantado en `http://127.0.0.1:8000`
  - Frontend Vue (Vite): Levantado en `http://localhost:5174`
  - WordPress / XAMPP: Disponible en `http://localhost/RESERVASSAGARETXE`
- **Despliegue en Coolify:**
  - Se ha solucionado el error 502 Bad Gateway en Coolify ajustando el comando de inicio de Laravel a `php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000`.
  - Se han corregido los errores de Mixed Content añadiendo las variables de entorno `APP_URL`, `ASSET_URL` y `FORCE_HTTPS`.
  - Se ha modificado `web.php` para que la raíz `/` redirija al `dashboard` y al login, en vez de mostrar la página de bienvenida de Laravel.
  - La aplicación ya es visible correctamente en `https://app.sagaretxe.net`.
### 2026-05-11
- **Levantamiento de servicios:**
  - App Central Backend (API): Levantado en `http://127.0.0.1:8000`
  - Frontend Vue (Vite): Levantado en `http://localhost:5173`
  - WordPress / XAMPP: Disponible en `http://localhost/RESERVASSAGARETXE`
- **Desarrollo:**
  - Se ha añadido la funcionalidad de crear excepciones de horario **"Permanentes"** (aplicables todos los años) o **"Puntuales"**.
  - Se han añadido 4 botones rápidos en la pestaña de Libro de Reservas para que los empleados puedan **"Cerrar Hoy"** o **"Cerrar Mañana"** (turnos de mañana y tarde) en un clic, creando excepciones puntuales de forma automatizada.


### 2026-05-10
- **Levantamiento de servicios:**
  - App Central Backend (API): Levantado en `http://127.0.0.1:8000`
  - Frontend Vue (Vite): Levantado en `http://localhost:5175`
  - WordPress / XAMPP: Disponible en `http://localhost/RESERVASSAGARETXE`
- **Planificación:** Elaborando plan de desarrollo para agregar nuevos campos de perfil al Club Sagaretxe, optimización de solapamiento de horarios y nueva gestión de correos electrónicos.
- **Rediseño UI/UX y Expansión Emails:**
  - **Vue (Reservas y Club):** Añadido menú horizontal para dividir plantillas, cabecera y footer de los correos automáticos. Añadidos los campos de "Desde Nombre" y "Desde Email", además del Asunto de todos los correos. Corregido el flex-box del Horario General en Reservas. Añadida la pestaña "Redirecciones" para definir URLs de confirmación/cancelación personalizadas.
  - **Plugins WP:** Rediseñado por completo el shortcode de reservas de mesa (ahora moderno, con grid layout). Rediseñado y ampliado el shortcode del Club Sagaretxe para incluir todos los campos del perfil extendido (DNI, Dirección, Preferencias).

### 2026-05-07
- **Reactivación de Servicios:**
  - App Central Backend (API): Levantado en `http://127.0.0.1:8000`
  - Frontend Vue (Vite): Levantado en `http://localhost:5174` (puerto 5173 estaba ocupado)
  - WordPress / XAMPP: Disponible en `http://localhost/RESERVASSAGARETXE`


### 2026-05-06
- **Nueva Arquitectura:** Se ha implementado la aplicación central (Laravel + Vue 3 + Inertia) que concentra toda la lógica de negocio (miembros, reservas, validaciones, PWA móvil para empleados).
- **Refactorización de WordPress:** Los plugins de WP se han adaptado como clientes API. Se limpió el plugin de reservas (ahora es solo frontend) y se creó un nuevo plugin `sagaretxe-club`.
- **Servicios Locales (Activos):**
  - App Central Backend (API): `http://127.0.0.1:8000`
  - Frontend Vue (PWA Empleados): `http://localhost:5173` (Accesible a través de Vite) o `http://127.0.0.1:8000/dashboard` (Compilado)
  - Base de datos MySQL: `sagaretxe_app`

### 2026-04-27
- **Ajustes de Emails:** Se crearon las vistas para configurar las plantillas de email (Asunto y cuerpo).
- **Flujo de correos:** 
  - Al realizar reserva se envía correo con enlaces para confirmar y cancelar.
  - Cronjob configurado para enviar recordatorios a falta de 12 horas y 6 horas.
  - Cronjob configurado para pedir reseñas 24 horas después de la cita.
- **Nuevos estados:** Se añadieron los estados `confirmada_cliente` y `cancelada_cliente` que se aplican automáticamente cuando el usuario interactúa con los links del correo, reflejándose visualmente (verde/rojo) en la lista de reservas.
- **Plano de sala:** Se ha modificado el sistema de mesas Drag&Drop (Sala) para permitir crear mesas redondas, modificando el CSS, JS y el guardado en base de datos. Se ha reemplazado el sistema de ventanas emergentes nativas (prompt/confirm) por un modal HTML personalizado para introducir nombre, capacidad y forma. También se ha incorporado la capacidad de hacer doble clic sobre una mesa existente para editar sus propiedades o eliminarla (incluso desasignándola de reservas existentes si se borra).
- **Servidor Local:** Proyecto disponible en `http://localhost/RESERVASSAGARETXE` (puerto 80 por defecto en XAMPP).
