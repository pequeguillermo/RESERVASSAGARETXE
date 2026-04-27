<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Schedule_Manager {

    /**
     * Devuelve los turnos disponibles para una fecha y número de comensales.
     */
    public static function get_available_slots( $date, $guests ) {
        global $wpdb;

        // 1. Obtener todos los turnos activos para el día de la semana de la fecha solicitada
        $day_of_week = date('w', strtotime($date)); // 0 (Sun) to 6 (Sat)
        
        $shifts_table = $wpdb->prefix . 'rrm_shifts';
        $shifts = $wpdb->get_results( "SELECT * FROM $shifts_table WHERE dias_activos LIKE '%$day_of_week%'" );

        if ( empty( $shifts ) ) {
            return []; // No hay turnos configurados para este día
        }

        // 2. Comprobar excepciones de cierre
        $exceptions_table = $wpdb->prefix . 'rrm_exceptions';
        $closed_exception = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $exceptions_table WHERE fecha = %s AND tipo = 'cerrado'", $date ) );
        if ( $closed_exception ) {
            return []; // El restaurante está cerrado ese día
        }

        $available_shifts = [];

        // 3. Calcular disponibilidad por turno
        $reservations_table = $wpdb->prefix . 'rrm_reservations';
        foreach ( $shifts as $shift ) {
            // Verificar si hay una capacidad especial para este día
            $special_capacity = $wpdb->get_var( $wpdb->prepare( "SELECT capacidad_especial FROM $exceptions_table WHERE fecha = %s AND tipo = 'capacidad_especial'", $date ) );
            $max_capacity = $special_capacity ? (int) $special_capacity : (int) $shift->capacidad_maxima;

            // Obtener ocupación actual para este turno y fecha (estado pending o confirmed)
            // IMPORTANTE: Canceladas o no-shows NO cuentan.
            $occupancy = $wpdb->get_var( $wpdb->prepare( "
                SELECT SUM(comensales) 
                FROM $reservations_table 
                WHERE fecha = %s AND turno_id = %d AND (estado = 'pending' OR estado = 'confirmed')
            ", $date, $shift->id ) );

            $occupancy = $occupancy ? (int) $occupancy : 0;
            $remaining = $max_capacity - $occupancy;

            if ( $remaining >= $guests ) {
                $available_shifts[] = [
                    'id' => $shift->id,
                    'nombre' => $shift->nombre,
                    'hora_inicio' => $shift->hora_inicio,
                    'hora_fin' => $shift->hora_fin,
                    'remaining' => $remaining
                ];
            }
        }

        return $available_shifts;
    }
}
