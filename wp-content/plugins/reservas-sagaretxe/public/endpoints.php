<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Endpoints {

    public static function init() {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
    }

    public static function register_routes() {
        // Endpoint to get available slots
        register_rest_route( 'rrm/v1', '/availability', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'get_availability' ],
            'permission_callback' => '__return_true'
        ]);

        // Endpoint to create a reservation
        register_rest_route( 'rrm/v1', '/reserve', [
            'methods' => 'POST',
            'callback' => [ __CLASS__, 'create_reservation' ],
            'permission_callback' => '__return_true'
        ]);

        // Endpoint for email confirmation
        register_rest_route( 'rrm/v1', '/confirm', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'confirm_reservation' ],
            'permission_callback' => '__return_true'
        ]);

        // Endpoint for email cancellation
        register_rest_route( 'rrm/v1', '/cancel', [
            'methods' => 'GET',
            'callback' => [ __CLASS__, 'cancel_reservation' ],
            'permission_callback' => '__return_true'
        ]);
    }

    public static function get_availability( WP_REST_Request $request ) {
        $date = sanitize_text_field( $request->get_param( 'date' ) );
        $guests = (int) $request->get_param( 'guests' );

        if ( empty( $date ) || $guests <= 0 ) {
            return new WP_Error( 'invalid_params', 'Fecha o comensales inválidos', [ 'status' => 400 ] );
        }

        require_once RRM_PLUGIN_DIR . 'includes/class-schedule-manager.php';
        $slots = RRM_Schedule_Manager::get_available_slots( $date, $guests );

        return rest_ensure_response( $slots );
    }

    public static function create_reservation( WP_REST_Request $request ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rrm_reservations';
        $logs_table = $wpdb->prefix . 'rrm_logs';

        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            $params = $request->get_body_params();
        }

        $date = sanitize_text_field( $params['date'] ?? '' );
        $shift_id = (int) ($params['shift_id'] ?? 0);
        $guests = (int) ($params['guests'] ?? 0);
        $name = sanitize_text_field( $params['name'] ?? '' );
        $email = sanitize_email( $params['email'] ?? '' );
        $phone = sanitize_text_field( $params['phone'] ?? '' );

        if ( empty($date) || empty($shift_id) || empty($guests) || empty($name) || empty($email) || empty($phone) ) {
            return new WP_Error( 'missing_fields', 'Faltan campos obligatorios', [ 'status' => 400 ] );
        }

        // Check Graylist
        require_once RRM_PLUGIN_DIR . 'includes/class-reservation-manager.php';
        $no_show_count = RRM_Reservation_Manager::is_email_graylisted( $email );
        if ( $no_show_count >= 2 ) {
            return new WP_Error( 'graylisted', 'Tu cuenta ha sido suspendida temporalmente por múltiples no-shows. Por favor, contacta con el restaurante.', [ 'status' => 403 ] );
        }

        // Validate availability again to prevent overbooking
        require_once RRM_PLUGIN_DIR . 'includes/class-schedule-manager.php';
        $slots = RRM_Schedule_Manager::get_available_slots( $date, $guests );
        $shift_valid = false;
        $hora_inicio = '';
        $hora_fin = '';
        foreach ( $slots as $slot ) {
            if ( $slot['id'] == $shift_id ) {
                $shift_valid = true;
                $hora_inicio = $slot['hora_inicio'];
                $hora_fin = $slot['hora_fin'];
                break;
            }
        }

        if ( ! $shift_valid ) {
            return new WP_Error( 'no_availability', 'El turno seleccionado ya no tiene disponibilidad.', [ 'status' => 400 ] );
        }

        // Create reservation as pending
        $inserted = $wpdb->insert( $table, [
            'nombre' => $name,
            'email' => $email,
            'telefono' => $phone,
            'fecha' => $date,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin,
            'turno_id' => $shift_id,
            'comensales' => $guests,
            'estado' => 'pending'
        ] );

        if ( ! $inserted ) {
            return new WP_Error( 'db_error', 'No se pudo guardar la reserva.', [ 'status' => 500 ] );
        }

        $reservation_id = $wpdb->insert_id;

        // Log action
        $wpdb->insert( $logs_table, [
            'reservation_id' => $reservation_id,
            'accion' => 'created',
            'usuario' => 'cliente',
            'descripcion' => 'Reserva creada como pending desde frontend.'
        ]);

        // Send email
        require_once RRM_PLUGIN_DIR . 'includes/class-email-manager.php';
        RRM_Email_Manager::send_confirmation_email( $reservation_id );

        return rest_ensure_response( [ 'success' => true, 'message' => 'Reserva creada. Revisa tu email para confirmarla.' ] );
    }

    public static function confirm_reservation( WP_REST_Request $request ) {
        global $wpdb;
        $token = sanitize_text_field( $request->get_param( 'token' ) );

        if ( empty( $token ) ) {
            wp_die( 'Token inválido' );
        }

        $table = $wpdb->prefix . 'rrm_reservations';
        $reservation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE confirmation_token = %s", $token ) );

        if ( ! $reservation ) {
            wp_die( 'El token no existe o ha expirado.' );
        }

        if ( $reservation->estado === 'confirmada_cliente' ) {
            wp_die( 'Esta reserva ya estaba confirmada. ¡Te esperamos!' );
        }

        if ( $reservation->estado !== 'pending' ) {
            wp_die( 'No se puede confirmar esta reserva. Estado actual: ' . esc_html($reservation->estado) );
        }

        // Update state to confirmada_cliente
        $wpdb->update(
            $table,
            [ 'estado' => 'confirmada_cliente' ],
            [ 'id' => $reservation->id ]
        );

        // Log action
        $logs_table = $wpdb->prefix . 'rrm_logs';
        $wpdb->insert( $logs_table, [
            'reservation_id' => $reservation->id,
            'accion' => 'confirmed',
            'usuario' => 'cliente',
            'descripcion' => 'Reserva confirmada mediante email link.'
        ]);

        wp_die( '<h1>¡Reserva confirmada!</h1><p>Tu reserva ha sido confirmada con éxito. Te esperamos el ' . esc_html($reservation->fecha) . ' a las ' . esc_html($reservation->hora_inicio) . '.</p>' );
    }

    public static function cancel_reservation( WP_REST_Request $request ) {
        global $wpdb;
        $token = sanitize_text_field( $request->get_param( 'token' ) );

        if ( empty( $token ) ) {
            wp_die( 'Token inválido' );
        }

        $table = $wpdb->prefix . 'rrm_reservations';
        $reservation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE cancel_token = %s", $token ) );

        if ( ! $reservation ) {
            wp_die( 'El token no existe o ha expirado.' );
        }

        if ( $reservation->estado === 'cancelada_cliente' ) {
            wp_die( 'Esta reserva ya estaba cancelada.' );
        }

        // Update state to cancelada_cliente
        $wpdb->update(
            $table,
            [ 'estado' => 'cancelada_cliente' ],
            [ 'id' => $reservation->id ]
        );

        // Log action
        $logs_table = $wpdb->prefix . 'rrm_logs';
        $wpdb->insert( $logs_table, [
            'reservation_id' => $reservation->id,
            'accion' => 'cancelled',
            'usuario' => 'cliente',
            'descripcion' => 'Reserva cancelada mediante email link.'
        ]);

        wp_die( '<h1>Reserva cancelada</h1><p>Tu reserva para el ' . esc_html($reservation->fecha) . ' a las ' . esc_html($reservation->hora_inicio) . ' ha sido cancelada correctamente.</p>' );
    }
}
