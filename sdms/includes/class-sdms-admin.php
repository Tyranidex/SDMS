<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SDMS_Admin
 *
 * Handles admin-related functionalities, including enqueueing admin assets
 * and modifying the document edit screen.
 */
class SDMS_Admin {

    public function __construct() {
        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Modify the CPT document edit screen
        add_action( 'add_meta_boxes_sdms_document', array( $this, 'replace_featured_image_metabox' ), 20 );

        // Save the custom featured image (file type icon)
        add_action( 'save_post', array( $this, 'save_custom_featured_image' ) );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        global $post_type;
        if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'sdms_document' === $post_type || 'settings_page_sdms-settings' === $hook ) {
            wp_enqueue_style( 'sdms-admin-styles', SDMS_PLUGIN_URL . 'assets/css/sdms-admin-styles.css' );
            wp_enqueue_script( 'sdms-admin-script', SDMS_PLUGIN_URL . 'assets/js/sdms-admin-script.js', array( 'jquery', 'wp-mediaelement' ), '1.0.0', true );

            // Localize script with necessary data
            wp_localize_script( 'sdms-admin-script', 'sdmsAdmin', array(
                'title'                => __( 'Choose Icon', 'sdms' ),
                'button'               => __( 'Use this icon', 'sdms' ),
                'remove_label'         => __( 'Remove', 'sdms' ),
                'reset_label'          => __( 'Reset', 'sdms' ),
                'add_language_alert'   => __( 'Please select a language to add.', 'sdms' ),
                'available_languages'  => $this->get_available_languages(),
                'default_icons'        => $this->get_default_file_type_icons(),
            ) );
        }
    }

    /**
     * Replace the featured image metabox with custom file type image selection.
     */
    public function replace_featured_image_metabox() {
        // Remove the default featured image metabox
        remove_meta_box( 'postimagediv', 'sdms_document', 'side' );

        // Add a new metabox for file type image selection
        add_meta_box(
            'sdms_file_type_image',
            __( 'File Type Image', 'sdms' ),
            array( $this, 'render_file_type_image_metabox' ),
            'sdms_document',
            'side',
            'default'
        );
    }

    /**
     * Render the file type image metabox.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_file_type_image_metabox( $post ) {
        // Add a nonce field for security
        wp_nonce_field( 'sdms_save_file_type_image', 'sdms_file_type_image_nonce' );

        // Get the selected file type image
        $selected_image = get_post_meta( $post->ID, '_sdms_file_type_image', true );

        // Define available file types and labels
        $file_types = array(
            'pdf'   => __( 'PDF', 'sdms' ),
            'word'  => __( 'Word Document', 'sdms' ),
            'excel' => __( 'Excel Spreadsheet', 'sdms' ),
            'image' => __( 'Image', 'sdms' ),
            'video' => __( 'Video', 'sdms' ),
            'psd'   => __( 'Photoshop File', 'sdms' ),
            'ai'    => __( 'Illustrator File', 'sdms' ),
        );

        // Get custom file type icons from settings
        $file_type_icons = get_option( 'sdms_file_type_icons', array() );

        // Display file type options
        echo '<div class="sdms-file-type-image-options">';
        foreach ( $file_types as $key => $label ) {
            // Determine the icon URL
            $icon_url = isset( $file_type_icons[ $key ] ) ? $file_type_icons[ $key ] : SDMS_PLUGIN_URL . 'assets/images/icons/' . $key . '.png';

            // Output radio buttons with icons
            echo '<label>';
            echo '<input type="radio" name="sdms_file_type_image" value="' . esc_attr( $key ) . '" ' . checked( $selected_image, $key, false ) . '>';
            echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $label ) . '" style="display: block; max-width: 50px; max-height: 50px;">';
            echo '<span>' . esc_html( $label ) . '</span>';
            echo '</label>';
        }
        echo '</div>';
    }

    /**
     * Save the custom featured image (file type icon) when the post is saved.
     *
     * @param int $post_id The ID of the current post.
     */
    public function save_custom_featured_image( $post_id ) {
        // Verify the nonce
        if ( ! isset( $_POST['sdms_file_type_image_nonce'] ) || ! wp_verify_nonce( $_POST['sdms_file_type_image_nonce'], 'sdms_save_file_type_image' ) ) {
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

        // Sanitize and save the selected file type image
        $file_type_image = sanitize_text_field( $_POST['sdms_file_type_image'] );
        update_post_meta( $post_id, '_sdms_file_type_image', $file_type_image );
    }

    /**
     * Get available languages from JSON file.
     *
     * @return array
     */
    private function get_available_languages() {
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
     * Get default file type icons.
     *
     * @return array
     */
    private function get_default_file_type_icons() {
        $file_types = array( 'pdf', 'word', 'excel', 'image', 'video', 'psd', 'ai' );
        $icons = array();
        foreach ( $file_types as $type ) {
            $icons[ $type ] = SDMS_PLUGIN_URL . 'assets/images/icons/' . $type . '.png';
        }
        return $icons;
    }
}
