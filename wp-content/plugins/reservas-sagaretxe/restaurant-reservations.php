<?php
/**
 * Plugin Name: Reservas Sagaretxe
 * Plugin URI:  https://lallavedetupyme.com
 * Description: sistema de gestión de reservas realizado para sagaretxe por La llave de tu pyme
 * Version:     1.0.0
 * Author:      Guillermo Gutiérrez Vázquez
 * Text Domain: rrm-reservations
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

define( 'RRM_VERSION', '1.0.1' );
define( 'RRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary classes
require_once RRM_PLUGIN_DIR . 'includes/class-db-installer.php';
require_once RRM_PLUGIN_DIR . 'admin/admin-menu.php';
require_once RRM_PLUGIN_DIR . 'includes/class-schedule-manager.php';
require_once RRM_PLUGIN_DIR . 'includes/class-email-manager.php';
require_once RRM_PLUGIN_DIR . 'includes/class-reservation-manager.php';
require_once RRM_PLUGIN_DIR . 'includes/class-cron-manager.php';
require_once RRM_PLUGIN_DIR . 'public/shortcodes.php';
require_once RRM_PLUGIN_DIR . 'public/endpoints.php';

// Activation Hook
register_activation_hook( __FILE__, [ 'RRM_DB_Installer', 'install' ] );

// Initialize plugin classes
function rrm_init() {
    RRM_Admin_Menu::init();
    RRM_Shortcodes::init();
    RRM_Endpoints::init();
    RRM_Cron_Manager::init();
}
add_action( 'plugins_loaded', 'rrm_init' );
