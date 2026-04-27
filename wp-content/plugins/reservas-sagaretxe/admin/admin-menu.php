<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RRM_Admin_Menu {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menus' ] );
        add_action( 'admin_post_rrm_change_status', [ __CLASS__, 'handle_status_change' ] );
        add_action( 'admin_post_rrm_remove_graylist', [ __CLASS__, 'handle_remove_graylist' ] );
        add_action( 'admin_post_rrm_save_shift', [ __CLASS__, 'handle_save_shift' ] );
        add_action( 'admin_post_rrm_delete_shift', [ __CLASS__, 'handle_delete_shift' ] );
        add_action( 'admin_post_rrm_save_schedule', [ __CLASS__, 'handle_save_schedule' ] );
        add_action( 'admin_post_rrm_save_exception', [ __CLASS__, 'handle_save_exception' ] );
        add_action( 'admin_post_rrm_delete_exception', [ __CLASS__, 'handle_delete_exception' ] );
        add_action( 'admin_post_rrm_save_settings', [ __CLASS__, 'handle_save_settings' ] );
        add_action( 'admin_post_rrm_save_email_settings', [ __CLASS__, 'handle_save_email_settings' ] );
        
        add_action( 'wp_ajax_rrm_save_layout', [ __CLASS__, 'ajax_save_layout' ] );
        add_action( 'wp_ajax_rrm_assign_table', [ __CLASS__, 'ajax_assign_table' ] );
        add_action( 'wp_ajax_rrm_delete_table', [ __CLASS__, 'ajax_delete_table' ] );
    }

    public static function ajax_delete_table() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error('No permission');
        
        global $wpdb;
        $table = $wpdb->prefix . 'rrm_tables';
        $table_id = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
        
        if ( $table_id > 0 ) {
            $wpdb->delete( $table, [ 'id' => $table_id ] );
            
            // Opcionalmente desasignar la mesa de cualquier reserva futura
            $res_table = $wpdb->prefix . 'rrm_reservations';
            $wpdb->update( $res_table, [ 'table_id' => null ], [ 'table_id' => $table_id ] );
            
            wp_send_json_success();
        }
        
        wp_send_json_error('Invalid table ID');
    }

    public static function ajax_save_layout() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error('No permission');
        
        global $wpdb;
        $table = $wpdb->prefix . 'rrm_tables';
        $layout = isset($_POST['layout']) ? $_POST['layout'] : [];
        
        foreach ( $layout as $tbl ) {
            $forma = isset($tbl['forma']) ? sanitize_text_field($tbl['forma']) : 'rect';
            if ( isset($tbl['id']) ) {
                $wpdb->update( $table, [
                    'pos_x' => floatval($tbl['x']),
                    'pos_y' => floatval($tbl['y']),
                    'ancho' => floatval($tbl['w']),
                    'alto' => floatval($tbl['h']),
                    'forma' => $forma
                ], [ 'id' => intval($tbl['id']) ] );
            } else {
                $wpdb->insert( $table, [
                    'nombre' => sanitize_text_field($tbl['name']),
                    'capacidad' => intval($tbl['cap']),
                    'pos_x' => floatval($tbl['x']),
                    'pos_y' => floatval($tbl['y']),
                    'ancho' => floatval($tbl['w']),
                    'alto' => floatval($tbl['h']),
                    'forma' => $forma
                ]);
            }
        }
        wp_send_json_success();
    }

    public static function ajax_assign_table() {
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error('No permission');
        
        $res_id = intval($_POST['reservation_id']);
        $table_id = intval($_POST['table_id']);
        
        if ( $res_id && $table_id ) {
            global $wpdb;
            $wpdb->update( 
                $wpdb->prefix . 'rrm_reservations', 
                [ 'table_id' => $table_id ], 
                [ 'id' => $res_id ] 
            );
            $wpdb->insert( $wpdb->prefix . 'rrm_logs', [
                'reservation_id' => $res_id,
                'accion' => 'assigned_table',
                'usuario' => wp_get_current_user()->user_login,
                'descripcion' => "Mesa $table_id asignada."
            ]);
            wp_send_json_success();
        }
        wp_send_json_error('Missing data');
    }

    public static function handle_remove_graylist() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos.' );
        }

        $graylist_id = isset( $_POST['graylist_id'] ) ? intval( $_POST['graylist_id'] ) : 0;
        if ( $graylist_id ) {
            global $wpdb;
            $table = $wpdb->prefix . 'rrm_graylist';
            $wpdb->delete( $table, [ 'id' => $graylist_id ] );
        }

        wp_redirect( admin_url( 'admin.php?page=rrm-graylist' ) );
        exit;
    }

    public static function handle_status_change() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'No tienes permisos.' );
        }

        $reservation_id = isset( $_POST['reservation_id'] ) ? intval( $_POST['reservation_id'] ) : 0;
        $new_status = isset( $_POST['new_status'] ) ? sanitize_text_field( $_POST['new_status'] ) : '';

        if ( $reservation_id && $new_status ) {
            require_once RRM_PLUGIN_DIR . 'includes/class-reservation-manager.php';
            RRM_Reservation_Manager::change_status( $reservation_id, $new_status, wp_get_current_user()->user_login );
        }

        wp_redirect( admin_url( 'admin.php?page=rrm-reservations' ) );
        exit;
    }

    public static function handle_save_shift() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        check_admin_referer( 'rrm_save_shift_action', 'rrm_save_shift_nonce' );

        global $wpdb;
        $table = $wpdb->prefix . 'rrm_shifts';
        
        $data = [
            'nombre' => sanitize_text_field($_POST['nombre']),
            'hora_inicio' => sanitize_text_field($_POST['hora_inicio']),
            'hora_fin' => sanitize_text_field($_POST['hora_fin']),
            'capacidad_maxima' => intval($_POST['capacidad_maxima']),
            'dias_activos' => isset($_POST['dias']) ? implode(',', array_map('intval', $_POST['dias'])) : ''
        ];

        if ( !empty($_POST['shift_id']) ) {
            $wpdb->update($table, $data, ['id' => intval($_POST['shift_id'])]);
        } else {
            $wpdb->insert($table, $data);
        }

        wp_redirect( admin_url( 'admin.php?page=rrm-shifts&msg=saved' ) );
        exit;
    }

    public static function handle_delete_shift() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        if ( !empty($_GET['id']) ) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'rrm_shifts', ['id' => intval($_GET['id'])]);
        }
        wp_redirect( admin_url( 'admin.php?page=rrm-shifts&msg=deleted' ) );
        exit;
    }

    public static function handle_save_schedule() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        check_admin_referer( 'rrm_save_schedule_action', 'rrm_save_schedule_nonce' );

        global $wpdb;
        $table = $wpdb->prefix . 'rrm_schedule';
        
        foreach ( $_POST['schedule'] as $dia => $data ) {
            $wpdb->replace($table, [
                'dia_semana' => intval($dia),
                'hora_apertura' => sanitize_text_field($data['apertura']),
                'hora_cierre' => sanitize_text_field($data['cierre']),
                'is_closed' => isset($data['closed']) ? 1 : 0
            ]);
        }

        wp_redirect( admin_url( 'admin.php?page=rrm-schedule&msg=saved' ) );
        exit;
    }

    public static function handle_save_exception() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        check_admin_referer( 'rrm_save_exception_action', 'rrm_save_exception_nonce' );

        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'rrm_exceptions', [
            'fecha' => sanitize_text_field($_POST['fecha']),
            'tipo' => sanitize_text_field($_POST['tipo']),
            'capacidad_especial' => isset($_POST['capacidad_especial']) ? intval($_POST['capacidad_especial']) : null
        ]);

        wp_redirect( admin_url( 'admin.php?page=rrm-exceptions&msg=saved' ) );
        exit;
    }

    public static function handle_delete_exception() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        if ( !empty($_GET['id']) ) {
            global $wpdb;
            $wpdb->delete($wpdb->prefix . 'rrm_exceptions', ['id' => intval($_GET['id'])]);
        }
        wp_redirect( admin_url( 'admin.php?page=rrm-exceptions&msg=deleted' ) );
        exit;
    }

    public static function handle_save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        check_admin_referer( 'rrm_save_settings_action', 'rrm_save_settings_nonce' );

        update_option('rrm_email_sender_name', sanitize_text_field($_POST['rrm_email_sender_name']));
        update_option('rrm_email_sender_email', sanitize_email($_POST['rrm_email_sender_email']));
        update_option('rrm_max_party_size', intval($_POST['rrm_max_party_size']));

        wp_redirect( admin_url( 'admin.php?page=rrm-settings&msg=saved' ) );
        exit;
    }

    public static function handle_save_email_settings() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No tienes permisos.' );
        check_admin_referer( 'rrm_save_email_settings_action', 'rrm_save_email_settings_nonce' );

        update_option('rrm_email_sender_name', sanitize_text_field($_POST['rrm_email_sender_name']));
        update_option('rrm_email_sender_email', sanitize_email($_POST['rrm_email_sender_email']));

        update_option('rrm_conf_subject', sanitize_text_field($_POST['rrm_conf_subject']));
        update_option('rrm_conf_body', sanitize_textarea_field($_POST['rrm_conf_body']));
        
        update_option('rrm_rem12_subject', sanitize_text_field($_POST['rrm_rem12_subject']));
        update_option('rrm_rem12_body', sanitize_textarea_field($_POST['rrm_rem12_body']));
        
        update_option('rrm_rem6_subject', sanitize_text_field($_POST['rrm_rem6_subject']));
        update_option('rrm_rem6_body', sanitize_textarea_field($_POST['rrm_rem6_body']));
        
        update_option('rrm_rev_subject', sanitize_text_field($_POST['rrm_rev_subject']));
        update_option('rrm_rev_body', sanitize_textarea_field($_POST['rrm_rev_body']));

        wp_redirect( admin_url( 'admin.php?page=rrm-emails&msg=saved' ) );
        exit;
    }


    public static function add_admin_menus() {
        add_menu_page(
            'Reservas',
            'Reservas',
            'manage_options',
            'rrm-reservations',
            [ __CLASS__, 'render_reservations_page' ],
            'dashicons-calendar-alt',
            25
        );

        add_submenu_page(
            'rrm-reservations',
            'Sala',
            'Sala',
            'manage_options',
            'rrm-floor-plan',
            [ __CLASS__, 'render_floor_plan_page' ]
        );

        add_submenu_page(
            'rrm-reservations',
            'Turnos',
            'Turnos',
            'manage_options',
            'rrm-shifts',
            [ __CLASS__, 'render_shifts_page' ]
        );

        add_submenu_page(
            'rrm-reservations',
            'Horarios',
            'Horarios',
            'manage_options',
            'rrm-schedule',
            [ __CLASS__, 'render_schedule_page' ]
        );

        add_submenu_page(
            'rrm-reservations',
            'Excepciones',
            'Excepciones',
            'manage_options',
            'rrm-exceptions',
            [ __CLASS__, 'render_exceptions_page' ]
        );

        add_submenu_page(
            'rrm-reservations',
            'Lista Gris',
            'Lista Gris',
            'manage_options',
            'rrm-graylist',
            [ __CLASS__, 'render_graylist_page' ]
        );

        add_submenu_page(
            'rrm-reservations',
            'Emails',
            'Emails',
            'manage_options',
            'rrm-emails',
            [ __CLASS__, 'render_email_settings_page' ]
        );
    }

    public static function render_reservations_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/reservations.php';
    }

    public static function render_floor_plan_page() {
        wp_enqueue_script( 'interactjs', 'https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js', [], null, true );
        wp_enqueue_script( 'rrm-floor-plan-js', RRM_PLUGIN_URL . 'admin/assets/js/floor-plan.js', ['interactjs'], RRM_VERSION, true );
        wp_localize_script( 'rrm-floor-plan-js', 'rrm_admin_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' )
        ]);
        wp_enqueue_style( 'rrm-floor-plan-css', RRM_PLUGIN_URL . 'admin/assets/css/floor-plan.css', [], RRM_VERSION );
        
        require_once RRM_PLUGIN_DIR . 'admin/views/floor-plan.php';
    }

    public static function render_shifts_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/shifts.php';
    }

    public static function render_schedule_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/schedule.php';
    }

    public static function render_exceptions_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/exceptions.php';
    }

    public static function render_graylist_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/graylist.php';
    }

    public static function render_settings_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/settings.php';
    }
    public static function render_email_settings_page() {
        require_once RRM_PLUGIN_DIR . 'admin/views/emails.php';
    }
}

