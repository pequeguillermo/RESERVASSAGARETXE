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
        <div id="rrm-reservation-app" class="rrm-container" style="max-width: 600px; margin: 2rem auto; font-family: 'Inter', system-ui, sans-serif; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;">
            <h2 style="text-align: center; color: #111827; margin-bottom: 5px; font-weight: 800; font-size: 1.8rem;">Reserva tu mesa</h2>
            <p style="text-align: center; color: #6b7280; font-size: 0.95rem; margin-bottom: 25px;">Completa los datos a continuación y asegura tu lugar.</p>
            
            <style>
                .rrm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .rrm-full { grid-column: span 2; }
                .rrm-form-group { display: flex; flex-direction: column; gap: 6px; }
                .rrm-label { font-weight: 600; font-size: 0.85rem; color: #374151; }
                .rrm-input { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; color: #111827; background: #f9fafb; transition: all 0.2s; outline: none; }
                .rrm-input:focus { border-color: #4f46e5; background: #fff; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
                .rrm-btn { width: 100%; padding: 14px; background-color: #111827; color: white; border: none; border-radius: 8px; font-size: 1.05rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s, transform 0.1s; }
                .rrm-btn:hover { background-color: #374151; }
                .rrm-btn:active { transform: scale(0.98); }
                .rrm-btn:disabled { background-color: #9ca3af; cursor: not-allowed; }
                @media (max-width: 480px) { .rrm-grid { grid-template-columns: 1fr; } .rrm-full { grid-column: span 1; } }
            </style>

            <form id="rrm-step-1" class="rrm-grid">
                <div class="rrm-form-group">
                    <label class="rrm-label">Fecha *</label>
                    <input type="date" id="rrm-date" class="rrm-input" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Hora *</label>
                    <select id="rrm-time" class="rrm-input" required disabled><option value="">Selecciona fecha primero</option></select>
                </div>
                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label">Comensales *</label>
                    <input type="number" id="rrm-guests" class="rrm-input" min="1" max="20" value="2" required>
                </div>
                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label">Nombre Completo *</label>
                    <input type="text" id="rrm-name" class="rrm-input" placeholder="Ej: María García" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Email *</label>
                    <input type="email" id="rrm-email" class="rrm-input" placeholder="tucorreo@ejemplo.com" required>
                </div>
                <div class="rrm-form-group">
                    <label class="rrm-label">Teléfono *</label>
                    <input type="tel" id="rrm-phone" class="rrm-input" placeholder="600 000 000" required>
                </div>
                <div class="rrm-form-group rrm-full">
                    <label class="rrm-label">Notas (Opcional)</label>
                    <textarea id="rrm-notes" class="rrm-input" rows="3" placeholder="Alergias, trona, preferencias..."></textarea>
                </div>
                <div class="rrm-full" style="margin-top: 10px;">
                    <button type="submit" id="rrm-btn-submit" class="rrm-btn">Confirmar Reserva</button>
                </div>
            </form>

            <div id="rrm-messages" style="margin-top: 20px; font-weight: bold; text-align: center; border-radius: 8px; padding: 10px; display: none;"></div>
        </div>
        <script>
            // JS handles everything, injected by wp_enqueue_script
        </script>
        <?php
        return ob_get_clean();
    }
}
