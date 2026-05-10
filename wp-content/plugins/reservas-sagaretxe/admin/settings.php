<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Admin {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
    }

    public static function add_admin_menu() {
        add_menu_page(
            'Reservas Sagaretxe',
            'Reservas',
            'manage_options',
            'reservas-sagaretxe',
            [__CLASS__, 'render_settings_page'],
            'dashicons-calendar-alt',
            25
        );
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Configuración de Reservas Sagaretxe</h1>
            <p>Bienvenido al panel de información del sistema de reservas de Sagaretxe.</p>
            
            <h2>Shortcodes Disponibles</h2>
            <p>Puedes usar el siguiente shortcode en cualquier página o post de WordPress para mostrar el formulario interactivo de reservas:</p>
            
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Formulario de Reservas</th>
                        <td>
                            <code>[restaurant_reservations]</code>
                            <p class="description">Muestra el selector de fecha, hora y datos de contacto conectado con la central de reservas Laravel.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
}
