<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get available languages from languages.json
 *
 * @return array
 */
function sdms_get_available_languages() {
    $json_file = SDMS_LANGUAGES_FILE;
    if ( file_exists( $json_file ) ) {
        $json_data = file_get_contents( $json_file );
        $languages = json_decode( $json_data, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            return $languages;
        }
    }
    return array();
}

/**
 * Get the flag URL for a given language code.
 *
 * @param string $code Language code (e.g., 'en', 'fr').
 * @return string URL of the flag image.
 */
function sdms_get_flag_url( $code ) {
    $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );

    if ( $selected_flag_icon_type === 'custom' ) {
        // Custom flag in theme
        $flag_file = get_stylesheet_directory() . '/sdms-flags/' . $code . '.png';
        $flag_url  = get_stylesheet_directory_uri() . '/sdms-flags/' . $code . '.png';
        if ( ! file_exists( $flag_file ) ) {
            // Use default flag if custom flag doesn't exist
            $flag_url = SDMS_PLUGIN_URL . 'assets/images/default-flag.png';
        }
    } else {
        // Plugin flag
        $flag_file = SDMS_PLUGIN_DIR . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
        $flag_url  = SDMS_PLUGIN_URL . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
        if ( ! file_exists( $flag_file ) ) {
            // Use default flag if plugin flag doesn't exist
            $flag_url = SDMS_PLUGIN_URL . 'assets/images/default-flag.png';
        }
    }

    return $flag_url;
}

/**
 * Generate the download URL for a document in a specific language.
 *
 * @param int    $post_id  The ID of the document post.
 * @param string $language (Optional) The language code. If not provided, the default download URL is returned.
 * @return string The download URL.
 */
function sdms_get_download_url( $post_id, $language = '' ) {
    $permalink = get_permalink( $post_id );

    if ( ! empty( $language ) ) {
        return trailingslashit( $permalink ) . 'download/' . $language;
    } else {
        return trailingslashit( $permalink ) . 'download';
    }
}

/**
 * Get the file type icon URL for a given file type.
 *
 * @param string $file_type The file type key (e.g., 'pdf', 'word').
 * @return string|null The icon URL or null if not found.
 */
function sdms_get_file_type_icon_url( $file_type ) {
    if ( ! $file_type ) {
        return null;
    }

    // Get custom icons from plugin options
    $file_type_icons = get_option( 'sdms_file_type_icons', array() );

    // Determine the icon URL
    if ( isset( $file_type_icons[ $file_type ] ) && ! empty( $file_type_icons[ $file_type ] ) ) {
        return $file_type_icons[ $file_type ];
    } else {
        // Use default icon if custom icon is not set
        $default_icon_path = SDMS_PLUGIN_DIR . 'assets/images/icons/' . $file_type . '.png';
        if ( file_exists( $default_icon_path ) ) {
            return SDMS_PLUGIN_URL . 'assets/images/icons/' . $file_type . '.png';
        }
    }

    return null;
}

/**
 * Display language flags with download links for a document.
 *
 * @param int $post_id The ID of the document post.
 */
function sdms_display_language_links( $post_id ) {
    $languages = get_option( 'sdms_languages', array() );
    if ( is_array( $languages ) && ! empty( $languages ) ) {
        echo '<div class="sdms-language-links">';
        foreach ( $languages as $code => $language ) {
            // Check if a file is associated with this language
            $file_id = get_post_meta( $post_id, 'sdms_file_' . $code, true );
            if ( $file_id ) {
                $download_url = sdms_get_download_url( $post_id, $code );
                $flag_url     = sdms_get_flag_url( $code );
                echo '<a href="' . esc_url( $download_url ) . '" target="_blank">';
                echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" class="sdms-flag-icon">';
                echo '</a>';
            }
        }
        echo '</div>';
    } else {
        echo '<p>' . esc_html__( 'No files available.', 'sdms' ) . '</p>';
    }
}

/**
 * Display download buttons for available languages.
 *
 * @param int $post_id The ID of the document post.
 */
function sdms_display_download_buttons( $post_id ) {
    $languages = get_option( 'sdms_languages', array() );
    if ( is_array( $languages ) && ! empty( $languages ) ) {
        echo '<div class="sdms-download-buttons">';
        foreach ( $languages as $code => $language ) {
            $file_id = get_post_meta( $post_id, 'sdms_file_' . $code, true );
            if ( $file_id ) {
                $download_url  = sdms_get_download_url( $post_id, $code );
                $language_name = $language['lang'];
                echo '<a href="' . esc_url( $download_url ) . '" class="sdms-download-button">' . sprintf( esc_html__( 'Download (%s)', 'sdms' ), esc_html( $language_name ) ) . '</a>';
            }
        }
        echo '</div>';
    } else {
        echo '<p>' . esc_html__( 'No files available for download.', 'sdms' ) . '</p>';
    }
}

/**
 * Get the default language code for a document.
 *
 * @param int $post_id The ID of the document post.
 * @return string The language code.
 */
function sdms_get_default_language( $post_id ) {
    // Preferred languages in order
    $preferred_languages = array( 'en', 'fr' );

    // Get the configured languages
    $languages = get_option( 'sdms_languages', array() );

    // Check if the preferred languages are available for this post
    foreach ( $preferred_languages as $lang_code ) {
        if ( isset( $languages[ $lang_code ] ) ) {
            $file_id = get_post_meta( $post_id, 'sdms_file_' . $lang_code, true );
            if ( $file_id ) {
                return $lang_code;
            }
        }
    }

    // If none of the preferred languages are available, return the first available language
    foreach ( $languages as $code => $language ) {
        $file_id = get_post_meta( $post_id, 'sdms_file_' . $code, true );
        if ( $file_id ) {
            return $code;
        }
    }

    // Default to 'en' if no files are found
    return 'en';
}
