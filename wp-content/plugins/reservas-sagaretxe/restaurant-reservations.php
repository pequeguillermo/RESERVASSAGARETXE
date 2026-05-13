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

define( 'RRM_VERSION', '1.2.0' );
define( 'RRM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RRM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary classes
require_once RRM_PLUGIN_DIR . 'public/shortcodes.php';
require_once RRM_PLUGIN_DIR . 'admin/settings.php';

// Initialize plugin classes
function rrm_init() {
    RRM_Shortcodes::init();
    RRM_Admin::init();
}
add_action( 'plugins_loaded', 'rrm_init' );
