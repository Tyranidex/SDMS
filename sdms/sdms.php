<?php
/*
Plugin Name: Simple Document Management System (SDMS)
Description: A plugin to manage documents with multilingual support.
Version: 1.0.1
Author: Dorian Renon
Text Domain: sdms
Domain Path: /languages
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants for directory and URL paths
define( 'sdms_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'sdms_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary class files
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-cpt.php';
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-admin.php';
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-frontend.php';
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-custom-fields.php';
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-settings.php';
require_once sdms_PLUGIN_DIR . 'includes/class-sdms-shortcodes.php';

// Initialize plugin classes
function sdms_init() {
    new sdms_CPT();
    new sdms_Admin();
    new sdms_Custom_Fields();
    new sdms_Settings();
    new sdms_Frontend();
    new sdms_Shortcodes();
}
add_action( 'plugins_loaded', 'sdms_init' );

/**
 * Activation hook: Set default options and flush rewrite rules.
 */
function sdms_activate() {
    // Load languages from JSON file
    $json_file = sdms_PLUGIN_DIR . 'languages.json';
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
                'country'     => sanitize_text_field( $language['country'] ),
                'flag'        => esc_url_raw( $language['flag'] ),
                'custom_flag' => '',
            );
            break;
        }
    }

    // If the English language is not found, use a fallback
    if ( empty( $default_language ) ) {
        $default_language['en'] = array(
            'country'     => 'United Kingdom',
            'flag'        => 'https://flagcdn.com/w20/gb.png', // Default flag URL
            'custom_flag' => '',
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

/**
 * Load a template part from the plugin's templates directory.
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialized template.
 */
function sdms_get_template_part( $slug, $name = null ) {
    $template = '';

    // Look in plugin's templates directory
    if ( isset( $name ) ) {
        $template = sdms_PLUGIN_DIR . "templates/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look for slug.php
    if ( ! file_exists( $template ) ) {
        $template = sdms_PLUGIN_DIR . "templates/{$slug}.php";
    }

    if ( $template && file_exists( $template ) ) {
        include $template;
    }
}