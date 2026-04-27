<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class RRM_Cron_Manager {

    public static function init() {
        add_action( 'rrm_daily_reminders_event', [ __CLASS__, 'process_reminders' ] );

        // Schedule cron if not exists
        if ( ! wp_next_scheduled( 'rrm_daily_reminders_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'rrm_daily_reminders_event' );
        }
    }

    public static function process_reminders() {
        global $wpdb;
        $reservations_table = $wpdb->prefix . 'rrm_reservations';
        $logs_table = $wpdb->prefix . 'rrm_logs';
        
        $current_time = current_time('timestamp');
        require_once RRM_PLUGIN_DIR . 'includes/class-email-manager.php';
        
        // Obtenemos todas las reservas confirmadas (no canceladas) donde falte enviar algún recordatorio
        // y la fecha no sea excesivamente lejana (para optimizar)
        $reservations = $wpdb->get_results( "
            SELECT * FROM $reservations_table 
            WHERE estado = 'confirmada_cliente' 
            AND (reminder_sent_12h = 0 OR reminder_sent_6h = 0 OR review_sent_24h = 0)
        " );

        foreach ( $reservations as $res ) {
            $reservation_time = strtotime( $res->fecha . ' ' . $res->hora_inicio );
            $diff_hours = ($reservation_time - $current_time) / 3600;
            
            // 1. Recordatorio 12h: Si faltan entre 6 y 12 horas
            if ( $res->reminder_sent_12h == 0 && $diff_hours > 6 && $diff_hours <= 12 ) {
                RRM_Email_Manager::send_reminder_email( $res, '12h' );
                $wpdb->update( $reservations_table, [ 'reminder_sent_12h' => 1 ], [ 'id' => $res->id ] );
                $wpdb->insert( $logs_table, [
                    'reservation_id' => $res->id,
                    'accion' => 'reminder_12h',
                    'usuario' => 'system',
                    'descripcion' => 'Recordatorio de 12h enviado automáticamente.'
                ]);
            }

            // 2. Recordatorio 6h: Si faltan entre 0 y 6 horas
            if ( $res->reminder_sent_6h == 0 && $diff_hours > 0 && $diff_hours <= 6 ) {
                RRM_Email_Manager::send_reminder_email( $res, '6h' );
                $wpdb->update( $reservations_table, [ 'reminder_sent_6h' => 1 ], [ 'id' => $res->id ] );
                $wpdb->insert( $logs_table, [
                    'reservation_id' => $res->id,
                    'accion' => 'reminder_6h',
                    'usuario' => 'system',
                    'descripcion' => 'Recordatorio de 6h enviado automáticamente.'
                ]);
            }

            // 3. Petición de Reseña (24h): Si han pasado más de 24 horas desde la hora de reserva
            // diff_hours será negativo cuando la reserva ya pasó
            if ( $res->review_sent_24h == 0 && $diff_hours <= -24 && $diff_hours > -48 ) {
                RRM_Email_Manager::send_review_email( $res );
                $wpdb->update( $reservations_table, [ 'review_sent_24h' => 1 ], [ 'id' => $res->id ] );
                $wpdb->insert( $logs_table, [
                    'reservation_id' => $res->id,
                    'accion' => 'review_24h',
                    'usuario' => 'system',
                    'descripcion' => 'Petición de reseña 24h enviada automáticamente.'
                ]);
            }
        }
    }
}
