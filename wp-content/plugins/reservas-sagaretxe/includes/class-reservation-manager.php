<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Reservation_Manager {

    public static function change_status( $reservation_id, $new_status, $user = 'admin' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'rrm_reservations';
        $logs_table = $wpdb->prefix . 'rrm_logs';
        $graylist_table = $wpdb->prefix . 'rrm_graylist';

        $reservation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $reservation_id ) );

        if ( ! $reservation ) {
            return false;
        }

        $old_status = $reservation->estado;

        if ( $old_status === $new_status ) {
            return true;
        }

        // Update status
        $wpdb->update(
            $table,
            [ 'estado' => $new_status ],
            [ 'id' => $reservation_id ]
        );

        // If it's a no-show, handle graylist
        if ( $new_status === 'no_show' ) {
            $email = $reservation->email;
            
            // Check if email is in graylist
            $in_graylist = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $graylist_table WHERE email = %s", $email ) );
            
            if ( $in_graylist ) {
                $wpdb->query( $wpdb->prepare( "UPDATE $graylist_table SET contador_no_show = contador_no_show + 1, ultima_incidencia = NOW() WHERE email = %s", $email ) );
            } else {
                $wpdb->insert( $graylist_table, [
                    'email' => $email,
                    'contador_no_show' => 1,
                    'ultima_incidencia' => current_time('mysql')
                ]);
            }
        }

        // Log the change
        $wpdb->insert( $logs_table, [
            'reservation_id' => $reservation_id,
            'accion' => $new_status,
            'usuario' => $user,
            'descripcion' => "Estado cambiado de '$old_status' a '$new_status'."
        ]);

        return true;
    }

    public static function is_email_graylisted( $email ) {
        global $wpdb;
        $graylist_table = $wpdb->prefix . 'rrm_graylist';
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT contador_no_show FROM $graylist_table WHERE email = %s", $email ) );
        
        return $count ? (int) $count : 0;
    }
}
