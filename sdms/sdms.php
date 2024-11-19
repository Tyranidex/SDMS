<?php
/*
Plugin Name: Simple Document Management System (SDMS)
Description: A plugin to manage documents with multilingual support.
Version: 1.0.2
Author: Dorian Renon
Text Domain: sdms
Domain Path: /languages
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants for directory and URL paths
define( 'SDMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SDMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SDMS_LANGUAGES_FILE', SDMS_PLUGIN_DIR . 'languages.json' );

// Include necessary class files
require_once SDMS_PLUGIN_DIR . 'includes/class-sdms-cpt.php';
require_once SDMS_PLUGIN_DIR . 'includes/class-sdms-admin.php';
require_once SDMS_PLUGIN_DIR . 'includes/class-sdms-frontend.php';
require_once SDMS_PLUGIN_DIR . 'includes/class-sdms-custom-fields.php';
require_once SDMS_PLUGIN_DIR . 'includes/class-sdms-settings.php';
require_once SDMS_PLUGIN_DIR . 'includes/sdms-functions.php';

// Initialize plugin classes
function sdms_init() {
    new SDMS_CPT();
    new SDMS_Admin();
    new SDMS_Custom_Fields();
    new SDMS_Settings();
    new SDMS_Frontend();
}
add_action( 'plugins_loaded', 'sdms_init' );

/**
 * Activation hook: Set default options and flush rewrite rules.
 */
function sdms_activate() {
    // Load languages from JSON file
    $json_file = SDMS_LANGUAGES_FILE;
    $available_languages = array();

    if ( file_exists( $json_file ) ) {
        $json_data = file_get_contents( $json_file );
        $available_languages = json_decode( $json_data, true );
    }

    // Find the English language data
    $default_language = array();
    foreach ( $available_languages as $language ) {
        if ( isset( $language['code'] ) && $language['code'] === 'en' ) {
            $default_language['en'] = array(
                'lang' => sanitize_text_field( $language['lang'] ),
                'flag' => esc_url_raw( $language['flag'] ),
            );
            break;
        }
    }

    // If the English language is not found, use a fallback
    if ( empty( $default_language ) ) {
        $default_language['en'] = array(
            'lang' => 'English',
            'flag' => SDMS_PLUGIN_URL . 'assets/images/flags/squared/en.png',
        );
    }

    // Set default languages if not already set
    if ( false === get_option( 'sdms_languages', false ) ) {
        update_option( 'sdms_languages', $default_language );
    }

    // Initialize the plugin to ensure post types and taxonomies are registered
    sdms_init();

    // Flush rewrite rules to register custom post types and rewrite rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'sdms_activate' );

/**
 * Deactivation hook: Flush rewrite rules.
 */
function sdms_deactivate() {
    // Flush rewrite rules to remove custom rewrites
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'sdms_deactivate' );
