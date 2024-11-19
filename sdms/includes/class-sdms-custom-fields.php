<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SDMS_Custom_Fields
 *
 * Handles custom fields for the SDMS plugin, specifically the language files associated with documents.
 */
class SDMS_Custom_Fields {

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

        // Enqueue media uploader script
        wp_enqueue_media();
        wp_enqueue_script( 'sdms-admin-script' ); // Ensure admin script is enqueued

        // Start the table
        echo '<table class="form-table sdms-language-files-table">';
        echo '<thead>';
        echo '<tr>';
        // Language headers with flags
        foreach ( $languages as $code => $language ) {
            $flag_url = sdms_get_flag_url( $code );
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
            $file_id  = get_post_meta( $post->ID, 'sdms_file_' . $code, true );
            $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

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
