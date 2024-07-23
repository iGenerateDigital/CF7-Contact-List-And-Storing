<?php
/*
Plugin Name: My Contacts
Description: Stores Contact Form 7 submissions and allows for managing and exporting contacts.
Version: 1.0
Author: iGenerate Digital
Author URI: https://igeneratedigital.com.au
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';

// Activation and deactivation hooks
register_activation_hook( __FILE__, 'cf7_storage_activate' );
register_deactivation_hook( __FILE__, 'cf7_storage_deactivate' );

// Activation function
function cf7_storage_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cf7_storage';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        form_title varchar(255) NOT NULL,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        website varchar(255) NOT NULL,
        company varchar(255) NOT NULL,
        phone varchar(20) NOT NULL,
        comments text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Deactivation function
function cf7_storage_deactivate() {
    // Perform any cleanup if necessary
}

// Enqueue scripts and styles for admin settings page
add_action( 'admin_enqueue_scripts', 'cf7_storage_enqueue_admin_scripts' );

function cf7_storage_enqueue_admin_scripts($hook) {
    if ($hook != 'toplevel_page_cf7-storage' && $hook != 'cf7-storage_page_cf7-storage-settings') {
        return;
    }

    wp_enqueue_style('cf7-storage-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css', array(), '1.0');
    wp_enqueue_script('cf7-storage-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', array('jquery'), '1.0', true);
}
?>
