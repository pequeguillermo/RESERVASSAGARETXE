<?php
/**
 * Plugin Name: Sagaretxe Club
 * Description: Plugin para registro de miembros de Sagaretxe Club conectado a la API Central.
 * Version: 1.0.0
 * Author: Guillermo Gutiérrez Vázquez
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode( 'sagaretxe_club_form', 'sc_render_club_form' );

add_action('admin_menu', 'sc_add_admin_menu');

function sc_add_admin_menu() {
    add_menu_page(
        'Club Sagaretxe',
        'Club Sagaretxe',
        'manage_options',
        'club-sagaretxe',
        'sc_render_settings_page',
        'dashicons-groups',
        26
    );
}

function sc_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración del Club Sagaretxe</h1>
        <p>Bienvenido al panel del Club Sagaretxe conectado a tu app central.</p>
        
        <h2>Shortcodes Disponibles</h2>
        <p>Puedes usar el siguiente shortcode en cualquier página o post de WordPress para mostrar el formulario de registro al club:</p>
        
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">Formulario del Club</th>
                    <td>
                        <code>[sagaretxe_club_form]</code>
                        <p class="description">Muestra el formulario de registro de miembros VIP del Club Sagaretxe.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php
}

function sc_render_club_form() {
    ob_start();
    ?>
    <div id="sc-club-app" style="max-width: 400px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; font-family: sans-serif;">
        <h2 style="text-align: center; color: #333;">Únete a Sagaretxe Club</h2>
        <p style="text-align: center; color: #666; font-size: 14px;">Regístrate para obtener ventajas exclusivas.</p>
        
        <form id="sc-club-form">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Nombre Completo:</label>
                <input type="text" id="sc-name" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Teléfono:</label>
                <input type="tel" id="sc-phone" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            
            <button type="submit" id="sc-btn-submit" style="width: 100%; padding: 10px; background-color: #2b6cb0; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Registrarme</button>
        </form>

        <div id="sc-messages" style="margin-top: 15px; text-align: center; font-weight: bold;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('sc-club-form');
        const messages = document.getElementById('sc-messages');
        const btn = document.getElementById('sc-btn-submit');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const name = document.getElementById('sc-name').value;
                const phone = document.getElementById('sc-phone').value;

                btn.disabled = true;
                btn.innerText = 'Registrando...';
                messages.innerHTML = '';

                fetch('http://127.0.0.1:8000/api/members', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        phone: phone
                    })
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerText = 'Registrarme';

                    if (data.member) {
                        form.style.display = 'none';
                        messages.innerHTML = '<span style="color: green;">¡Bienvenido al club! Tu registro ha sido completado con éxito. El personal validará tu acceso en la entrada.</span>';
                    } else if (data.message) {
                        messages.innerHTML = `<span style="color: red;">Error: ${data.message}</span>`;
                    } else {
                        messages.innerHTML = '<span style="color: red;">Ocurrió un error.</span>';
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerText = 'Registrarme';
                    messages.innerHTML = '<span style="color: red;">Error de conexión. Inténtalo de nuevo.</span>';
                    console.error(error);
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
