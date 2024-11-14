<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class sdms_Custom_Fields
 *
 * Handles custom fields for the SDMS plugin, specifically the language files associated with documents.
 */
class sdms_Custom_Fields {

    public function __construct() {
        // Add meta boxes for language files
        add_action( 'add_meta_boxes', array( $this, 'add_language_fields' ) );

        // Save the uploaded files
        add_action( 'save_post', array( $this, 'save_language_files' ) );
    }

    /**
     * Add the language files meta box to the 'sdms_document' post type.
     */
    public function add_language_fields() {
        add_meta_box(
            'sdms_language_files',
            __( 'Documents', 'sdms' ),
            array( $this, 'render_language_fields' ),
            'sdms_document',
            'normal',
            'default'
        );
    }

    /**
     * Render the language fields meta box content.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_language_fields( $post ) {
        // Add a nonce field for security
        wp_nonce_field( 'sdms_save_files', 'sdms_files_nonce' );

        // Get available languages from settings
        $languages = get_option( 'sdms_languages', array() );
        if ( ! is_array( $languages ) ) {
            $languages = array();
        }

        // Récupérer le type d'icône de drapeau sélectionné
        $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );

        // Si 'custom' est sélectionné mais que le dossier n'existe pas, revenir à 'squared'
        if ( $selected_flag_icon_type === 'custom' ) {
            $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
            if ( ! ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) ) {
                $selected_flag_icon_type = 'squared';
            }
        }

        // Include the media uploader script
        $this->enqueue_media_script();

        // Start the table
        echo '<table class="form-table sdms-language-files-table">';
        echo '<thead>';
        echo '<tr>';
        // Language headers with flags
        foreach ( $languages as $code => $language ) {
            // Déterminer le chemin de l'icône du drapeau
            if ( $selected_flag_icon_type === 'custom' ) {
                // Chemin vers le drapeau personnalisé dans le thème
                $flag_file = get_stylesheet_directory() . '/sdms-flags/' . $code . '.png';
                $flag_url  = get_stylesheet_directory_uri() . '/sdms-flags/' . $code . '.png';
                if ( ! file_exists( $flag_file ) ) {
                    // Si le drapeau personnalisé n'existe pas, utiliser un drapeau par défaut
                    $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                }
            } else {
                // Chemin vers le drapeau du plugin
                $flag_file = sdms_PLUGIN_DIR . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                $flag_url  = sdms_PLUGIN_URL . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                if ( ! file_exists( $flag_file ) ) {
                    // Si le drapeau n'existe pas, utiliser un drapeau par défaut
                    $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                }
            }

            echo '<th>';
            echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" style="vertical-align: middle; max-width: 24px; margin-right: 5px;">';
            echo esc_html( $language['lang'] );
            echo '</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Row 1: File status (View File / No file uploaded)
        echo '<tr>';
        foreach ( $languages as $code => $language ) {
            $file_id   = get_post_meta( $post->ID, 'sdms_file_' . $code, true );
            $file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';

            echo '<td>';
            if ( $file_url ) {
                echo '<a href="' . esc_url( $file_url ) . '" target="_blank">' . __( 'View File', 'sdms' ) . '</a>';
            } else {
                echo __( 'No file uploaded.', 'sdms' );
            }
            echo '</td>';
        }
        echo '</tr>';

        // Row 2: Actions (Upload / Remove buttons)
        echo '<tr>';
        foreach ( $languages as $code => $language ) {
            $file_id = get_post_meta( $post->ID, 'sdms_file_' . $code, true );

            echo '<td>';
            // Hidden input to store the file ID
            echo '<input type="hidden" name="sdms_file_' . esc_attr( $code ) . '" value="' . esc_attr( $file_id ) . '">';

            // Upload button
            $upload_style = $file_id ? 'display: none;' : '';
            echo '<button type="button" class="button sdms-upload-button" data-language="' . esc_attr( $code ) . '" style="' . esc_attr( $upload_style ) . '">';
            echo __( 'Upload File', 'sdms' );
            echo '</button>';

            // Remove button
            $remove_style = $file_id ? '' : 'display: none;';
            echo '<button type="button" class="button sdms-remove-file-button" data-language="' . esc_attr( $code ) . '" style="' . esc_attr( $remove_style ) . '">';
            echo __( 'Remove File', 'sdms' );
            echo '</button>';
            echo '</td>';
        }
        echo '</tr>';

        echo '</tbody>';
        echo '</table>';
    }

    /**
     * Enqueue script for media uploader.
     */
    private function enqueue_media_script() {
        wp_enqueue_media();
        wp_enqueue_script( 'sdms-media-uploader', sdms_PLUGIN_URL . 'assets/js/sdms-media-uploader.js', array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'sdms-media-uploader', 'sdmsUploader', array(
            'title'      => __( 'Choose File', 'sdms' ),
            'button'     => __( 'Use this file', 'sdms' ),
            'viewFile'   => __( 'View File', 'sdms' ),
            'removeFile' => __( 'Remove File', 'sdms' ),
            'noFile'     => __( 'No file uploaded.', 'sdms' ),
        ) );
    }

    /**
     * Save the uploaded files when the post is saved.
     *
     * @param int $post_id The ID of the current post.
     */
    public function save_language_files( $post_id ) {
        // Verify the nonce
        if ( ! isset( $_POST['sdms_files_nonce'] ) || ! wp_verify_nonce( $_POST['sdms_files_nonce'], 'sdms_save_files' ) ) {
            return;
        }

        // Check for autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check user permissions
        if ( 'sdms_document' !== $_POST['post_type'] || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Get available languages
        $languages = get_option( 'sdms_languages', array() );
        if ( ! is_array( $languages ) ) {
            $languages = array();
        }

        // Loop through languages and save the file IDs
        foreach ( $languages as $code => $language ) {
            $file_key = 'sdms_file_' . $code;
            $file_id  = isset( $_POST[ $file_key ] ) ? intval( $_POST[ $file_key ] ) : '';
            update_post_meta( $post_id, $file_key, $file_id );
        }
    }
}
