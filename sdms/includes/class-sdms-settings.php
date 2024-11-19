<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SDMS_Settings
 *
 * Handles the settings page for the SDMS plugin, allowing administrators
 * to configure languages, templates, and file type icons.
 */
class SDMS_Settings {

    public function __construct() {
        // Add settings page to the admin menu
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Add plugin settings page to the WordPress admin menu.
     */
    public function add_settings_page() {
        add_options_page(
            __( 'SDMS Settings', 'sdms' ),
            __( 'SDMS Settings', 'sdms' ),
            'manage_options',
            'sdms-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // Register settings for languages, templates, and file type icons
        register_setting( 'sdms_settings_group', 'sdms_languages', array( $this, 'sanitize_languages' ) );
        register_setting( 'sdms_settings_group', 'sdms_template' ); // Single post template
        register_setting( 'sdms_settings_group', 'sdms_archive_template' ); // Archive template
        register_setting( 'sdms_settings_group', 'sdms_taxonomy_template' );
        register_setting( 'sdms_settings_group', 'sdms_file_type_icons', array( $this, 'sanitize_file_type_icons' ) );
        register_setting( 'sdms_settings_group', 'sdms_flag_icon_type' );
    }

    /**
     * Sanitize the languages input from the settings form.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized languages array.
     */
    public function sanitize_languages( $input ) {
        $sanitized = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $code => $language ) {
                $sanitized_code = sanitize_text_field( $code );
                $sanitized[ $sanitized_code ] = array(
                    'lang' => sanitize_text_field( $language['lang'] ),
                    'flag' => esc_url_raw( $language['flag'] ),
                );
            }
        }
        return $sanitized;
    }

    /**
     * Sanitize the file type icons input from the settings form.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized file type icons array.
     */
    public function sanitize_file_type_icons( $input ) {
        $sanitized = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $type => $url ) {
                $sanitized_type = sanitize_text_field( $type );
                $sanitized[ $sanitized_type ] = esc_url_raw( $url );
            }
        }
        return $sanitized;
    }

    /**
     * Render the settings page content.
     */
    public function render_settings_page() {
        // Include the settings page template
        include SDMS_PLUGIN_DIR . 'includes/settings-page.php';
    }
}
