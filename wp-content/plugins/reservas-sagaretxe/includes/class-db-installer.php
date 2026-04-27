<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_DB_Installer {

    public static function install() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // wp_rrm_shifts
        $table_shifts = $wpdb->prefix . 'rrm_shifts';
        $sql_shifts = "CREATE TABLE $table_shifts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            hora_inicio time NOT NULL,
            hora_fin time NOT NULL,
            capacidad_maxima int(11) NOT NULL DEFAULT 0,
            dias_activos varchar(255) NOT NULL DEFAULT '1,2,3,4,5,6,0',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_shifts );

        // wp_rrm_reservations
        $table_reservations = $wpdb->prefix . 'rrm_reservations';
        $sql_reservations = "CREATE TABLE $table_reservations (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            telefono varchar(50) NOT NULL,
            fecha date NOT NULL,
            hora_inicio time NOT NULL,
            hora_fin time NOT NULL,
            turno_id bigint(20) unsigned DEFAULT NULL,
            comensales int(11) NOT NULL,
            estado varchar(50) NOT NULL DEFAULT 'pending',
            confirmation_token varchar(255) DEFAULT NULL,
            table_id bigint(20) unsigned DEFAULT NULL,
            notas_cliente text,
            notas_internas text,
            payment_status varchar(50) DEFAULT NULL,
            cancel_token varchar(255) DEFAULT NULL,
            reminder_sent_12h tinyint(1) NOT NULL DEFAULT 0,
            reminder_sent_6h tinyint(1) NOT NULL DEFAULT 0,
            review_sent_24h tinyint(1) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY estado (estado),
            KEY fecha (fecha)
        ) $charset_collate;";
        dbDelta( $sql_reservations );

        // wp_rrm_schedule
        $table_schedule = $wpdb->prefix . 'rrm_schedule';
        $sql_schedule = "CREATE TABLE $table_schedule (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            dia_semana tinyint(1) NOT NULL,
            hora_apertura time NOT NULL,
            hora_cierre time NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_schedule );

        // wp_rrm_exceptions
        $table_exceptions = $wpdb->prefix . 'rrm_exceptions';
        $sql_exceptions = "CREATE TABLE $table_exceptions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            fecha date NOT NULL,
            tipo varchar(50) NOT NULL,
            capacidad_especial int(11) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY fecha (fecha)
        ) $charset_collate;";
        dbDelta( $sql_exceptions );

        // wp_rrm_tables
        $table_tables = $wpdb->prefix . 'rrm_tables';
        $sql_tables = "CREATE TABLE $table_tables (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            nombre varchar(255) NOT NULL,
            capacidad int(11) NOT NULL DEFAULT 2,
            pos_x float NOT NULL DEFAULT 0,
            pos_y float NOT NULL DEFAULT 0,
            ancho float NOT NULL DEFAULT 50,
            alto float NOT NULL DEFAULT 50,
            forma varchar(50) NOT NULL DEFAULT 'rect',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta( $sql_tables );

        // wp_rrm_graylist
        $table_graylist = $wpdb->prefix . 'rrm_graylist';
        $sql_graylist = "CREATE TABLE $table_graylist (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            contador_no_show int(11) NOT NULL DEFAULT 0,
            ultima_incidencia datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";
        dbDelta( $sql_graylist );

        // wp_rrm_logs
        $table_logs = $wpdb->prefix . 'rrm_logs';
        $sql_logs = "CREATE TABLE $table_logs (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            reservation_id bigint(20) unsigned NOT NULL,
            accion varchar(100) NOT NULL,
            usuario varchar(100) NOT NULL DEFAULT 'system',
            descripcion text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY reservation_id (reservation_id)
        ) $charset_collate;";
        dbDelta( $sql_logs );

        self::check_and_create_default_data();
    }

    private static function check_and_create_default_data() {
        global $wpdb;
        $table_shifts = $wpdb->prefix . 'rrm_shifts';
        
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_shifts");
        if ($count == 0) {
            $wpdb->insert($table_shifts, [
                'nombre' => 'Almuerzo',
                'hora_inicio' => '13:00:00',
                'hora_fin' => '16:00:00',
                'capacidad_maxima' => 40
            ]);
            $wpdb->insert($table_shifts, [
                'nombre' => 'Cena',
                'hora_inicio' => '20:00:00',
                'hora_fin' => '23:30:00',
                'capacidad_maxima' => 40
            ]);
        }
    }
}
