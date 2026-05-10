<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Shortcodes {

    public static function init() {
        add_shortcode( 'restaurant_reservations', [ __CLASS__, 'render_reservation_form' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
    }

    public static function enqueue_scripts() {
        wp_register_script( 'rrm-reservation-form', RRM_PLUGIN_URL . 'public/assets/js/reservation-form.js', [], RRM_VERSION, true );
        wp_localize_script( 'rrm-reservation-form', 'rrm_ajax', [
            'rest_url' => esc_url_raw( rest_url() )
        ]);
    }

    public static function render_reservation_form() {
        wp_enqueue_script( 'rrm-reservation-form' );

        ob_start();
        ?>
        <div id="rrm-reservation-app" class="rrm-container" style="max-width: 500px; margin: 0 auto; font-family: sans-serif;">
            <h2>Haz tu Reserva</h2>
            <div id="rrm-step-1">
                <label>Fecha: <input type="date" id="rrm-date" min="<?php echo date('Y-m-d'); ?>" required></label><br><br>
                <label>Hora: <select id="rrm-time" required disabled><option value="">Selecciona fecha primero</option></select></label><br><br>
                <label>Comensales: <input type="number" id="rrm-guests" min="1" max="20" value="2" required></label><br><br>
                <label>Nombre: <input type="text" id="rrm-name" required></label><br><br>
                <label>Email: <input type="email" id="rrm-email" required></label><br><br>
                <label>Teléfono: <input type="tel" id="rrm-phone" required></label><br><br>
                <label>Notas: <textarea id="rrm-notes"></textarea></label><br><br>
                <button type="button" id="rrm-btn-submit" style="padding: 10px 20px; background-color: #0073aa; color: white; border: none;">Confirmar Reserva</button>
            </div>

            <div id="rrm-messages" style="margin-top: 20px; font-weight: bold;"></div>
        </div>

        </div>
        <script>
            // JS handles everything, injected by wp_enqueue_script
        </script>
        <?php
        return ob_get_clean();
    }
}
