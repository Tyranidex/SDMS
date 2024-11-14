<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class sdms_Shortcodes
 *
 * Handles the shortcodes for displaying file type icons and language flags.
 */
class sdms_Shortcodes {

    public function __construct() {
        // Register shortcodes
        add_shortcode( 'sdms_icon', array( $this, 'display_icon' ) );
        add_shortcode( 'sdms_flags', array( $this, 'display_flags' ) );
    }

    /**
     * Shortcode to display the file type icon.
     *
     * Usage: [sdms_icon]
     *
     * @return string HTML output for the icon.
     */
    public function display_icon() {
        global $post;
        if ( ! $post ) {
            return '';
        }

        // Get the selected file type
        $file_type = get_post_meta( $post->ID, '_sdms_file_type_image', true );
        $icon_url = '';

        if ( ! empty( $file_type ) ) {
            // Get custom icons from plugin options
            $file_type_icons = get_option( 'sdms_file_type_icons', array() );

            // Determine the icon URL
            if ( isset( $file_type_icons[ $file_type ] ) && ! empty( $file_type_icons[ $file_type ] ) ) {
                $icon_url = $file_type_icons[ $file_type ];
            } else {
                // Use default icon if custom icon is not set
                $default_icon_path = sdms_PLUGIN_DIR . 'assets/images/icons/' . $file_type . '.png';
                if ( file_exists( $default_icon_path ) ) {
                    $icon_url = sdms_PLUGIN_URL . 'assets/images/icons/' . $file_type . '.png';
                }
            }
        }

        if ( ! empty( $icon_url ) ) {
            return '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . ' icon" class="sdms-file-type-icon" />';
        }

        return '';
    }

    /**
     * Shortcode to display the flags with download links.
     *
     * Usage: [sdms_flags]
     *
     * @return string HTML output for the flags with download links.
     */
    public function display_flags() {
        global $post;
        if ( ! $post ) {
            return '';
        }

        $languages = get_option( 'sdms_languages', array() );
        $output = '';

        if ( is_array( $languages ) ) {
            foreach ( $languages as $code => $language ) {
                // Check if a file is uploaded for this language
                $file_id = get_post_meta( $post->ID, 'sdms_file_' . $code, true );
                if ( $file_id ) {
                    // Generate the download URL
                    $download_url = trailingslashit( get_permalink( $post->ID ) ) . 'download/' . $code;

                    // Determine the flag URL
                    $flag_url = '';
                    if ( ! empty( $language['flag'] ) ) {
                        $flag_url = $language['flag'];
                    }

                    // Build the output
                    if ( ! empty( $flag_url ) ) {
                        $output .= '<a href="' . esc_url( $download_url ) . '" target="_blank">';
                        $output .= '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . ' flag" class="sdms-flag-icon" />';
                        $output .= '</a> ';
                    }
                }
            }
        }

        return $output;
    }
}
