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
    <div id="sc-club-app" style="max-width: 600px; margin: 2rem auto; font-family: 'Inter', system-ui, sans-serif; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
        <h2 style="text-align: center; color: #111827; margin-bottom: 5px; font-weight: 800; font-size: 1.8rem;">Únete a Sagaretxe Club</h2>
        <p style="text-align: center; color: #6b7280; font-size: 0.95rem; margin-bottom: 25px;">Regístrate para obtener ventajas exclusivas.</p>
        
        <style>
            .sc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
            .sc-full { grid-column: span 2; }
            .sc-form-group { display: flex; flex-direction: column; gap: 6px; }
            .sc-label { font-weight: 600; font-size: 0.85rem; color: #374151; }
            .sc-input { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827; background: #f9fafb; transition: all 0.2s; outline: none; }
            .sc-input:focus { border-color: #4f46e5; background: #fff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
            .sc-btn { width: 100%; padding: 14px; background-color: #111827; color: white; border: none; border-radius: 8px; font-size: 1.05rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
            .sc-btn:hover { background-color: #374151; }
            .sc-btn:active { transform: scale(0.98); }
            .sc-btn:disabled { background-color: #9ca3af; cursor: not-allowed; }
            .sc-section-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin: 15px 0 5px 0; grid-column: span 2; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }
            @media (max-width: 480px) { .sc-grid { grid-template-columns: 1fr; } .sc-full, .sc-section-title { grid-column: span 1; } }
        </style>

        <form id="sc-club-form" class="sc-grid">
            <h3 class="sc-section-title">Datos Personales</h3>
            <div class="sc-form-group">
                <label class="sc-label">Nombre *</label>
                <input type="text" id="sc-name" class="sc-input" required>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">Apellidos *</label>
                <input type="text" id="sc-surname" class="sc-input" required>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">Teléfono *</label>
                <input type="tel" id="sc-phone" class="sc-input" required>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">Email *</label>
                <input type="email" id="sc-email" class="sc-input" required>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">Código Postal *</label>
                <input type="text" id="sc-postal-code" class="sc-input" required>
            </div>

            <h3 class="sc-section-title">Tus Preferencias *</h3>
            <div class="sc-form-group">
                <label class="sc-label">¿Barra o Sala? *</label>
                <select id="sc-pref-space" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="barra">Barra</option><option value="sala">Sala</option>
                </select>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">¿Carne o Pescado? *</label>
                <select id="sc-pref-food" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="carne">Carne</option><option value="pescado">Pescado</option>
                </select>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">¿Cerveza o Sidra? *</label>
                <select id="sc-pref-drink1" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="cerveza">Cerveza</option><option value="sidra">Sidra</option>
                </select>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">¿Vino Tinto o Blanco? *</label>
                <select id="sc-pref-drink2" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="tinto">Vino Tinto</option><option value="blanco">Vino Blanco</option>
                </select>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">¿Entre semana o fin de semana? *</label>
                <select id="sc-pref-time" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="semana">Entre semana</option><option value="finde">Fin de semana</option>
                </select>
            </div>
            <div class="sc-form-group">
                <label class="sc-label">¿Cómo nos ha conocido? *</label>
                <select id="sc-how-knew-us" class="sc-input" required>
                    <option value="">Selecciona...</option><option value="prensa">Prensa</option><option value="tv">TV</option><option value="internet">Internet</option><option value="conocido">Por un conocido</option><option value="vecino">Vecino del barrio</option>
                </select>
            </div>
            
            <div class="sc-full" style="margin-top: 15px;">
                <button type="submit" id="sc-btn-submit" class="sc-btn">Registrarme al Club</button>
            </div>
        </form>

        <div id="sc-messages" style="margin-top: 20px; font-weight: bold; text-align: center; border-radius: 8px; padding: 10px; display: none;"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('sc-club-form');
        const messages = document.getElementById('sc-messages');
        const btn = document.getElementById('sc-btn-submit');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const payload = {
                    name: document.getElementById('sc-name').value,
                    surname: document.getElementById('sc-surname').value,
                    phone: document.getElementById('sc-phone').value,
                    email: document.getElementById('sc-email').value,
                    postal_code: document.getElementById('sc-postal-code').value,
                    pref_space: document.getElementById('sc-pref-space').value,
                    pref_food: document.getElementById('sc-pref-food').value,
                    pref_drink1: document.getElementById('sc-pref-drink1').value,
                    pref_drink2: document.getElementById('sc-pref-drink2').value,
                    pref_time: document.getElementById('sc-pref-time').value,
                    how_knew_us: document.getElementById('sc-how-knew-us').value,
                };

                btn.disabled = true;
                btn.innerText = 'Registrando...';
                messages.style.display = 'none';

                fetch('https://app.sagaretxe.net/api/members', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerText = 'Registrarme al Club';
                    messages.style.display = 'block';

                    if (data.member) {
                        form.style.display = 'none';
                        messages.style.backgroundColor = '#ecfdf5';
                        messages.style.color = '#047857';
                        messages.style.border = '1px solid #a7f3d0';
                        messages.innerHTML = '¡Bienvenido al club Sagaretxe! Indica que perteneces al club facilitando tu teléfono y te aplicaremos tu descuento.';
                    } else if (data.message) {
                        messages.style.backgroundColor = '#fef2f2';
                        messages.style.color = '#b91c1c';
                        messages.style.border = '1px solid #fecaca';
                        messages.innerHTML = `Error: ${data.message}`;
                    } else {
                        messages.style.backgroundColor = '#fef2f2';
                        messages.style.color = '#b91c1c';
                        messages.style.border = '1px solid #fecaca';
                        messages.innerHTML = 'Ocurrió un error inesperado.';
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.innerText = 'Registrarme al Club';
                    messages.style.display = 'block';
                    messages.style.backgroundColor = '#fef2f2';
                    messages.style.color = '#b91c1c';
                    messages.style.border = '1px solid #fecaca';
                    messages.innerHTML = 'Error de conexión. Inténtalo de nuevo.';
                    console.error(error);
                });
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
