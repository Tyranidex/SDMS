<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SDMS_Access_Control
 *
 * Handles access control for SDMS documents, allowing administrators
 * to restrict document visibility based on custom roles and specific users.
 */
class SDMS_Access_Control {

    public function __construct() {
        // Add meta box to the document edit screen
        add_action( 'add_meta_boxes', array( $this, 'add_access_control_meta_box' ) );

        // Save the access control settings
        add_action( 'save_post', array( $this, 'save_access_control_settings' ) );

        // Handle AJAX user search
        add_action( 'wp_ajax_sdms_user_search', array( $this, 'handle_user_search_ajax' ) );

        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add the Access Control meta box to the sdms_document post type.
     */
    public function add_access_control_meta_box() {
        add_meta_box(
            'sdms_access_control',
            __( 'Access Control', 'sdms' ),
            array( $this, 'render_access_control_meta_box' ),
            'sdms_document',
            'side',
            'default'
        );
    }

    /**
     * Render the Access Control meta box content.
     *
     * @param WP_Post $post The current post object.
     */
    public function render_access_control_meta_box( $post ) {
        wp_nonce_field( 'sdms_save_access_control', 'sdms_access_control_nonce' );

        // Retrieve saved data
        $allowed_roles = get_post_meta( $post->ID, '_sdms_allowed_roles', true );
        $allowed_users = get_post_meta( $post->ID, '_sdms_allowed_users', true );

        if ( ! is_array( $allowed_users ) ) {
            $allowed_users = array();
        }

        // Get custom roles
        $sdms_user_roles = get_option( 'sdms_user_roles', array() );

        // Display role checkboxes
        echo '<p><strong>' . __( 'Allowed Roles', 'sdms' ) . '</strong></p>';
        if ( ! empty( $sdms_user_roles ) ) {
            foreach ( $sdms_user_roles as $role_slug => $role_name ) {
                echo '<label><input type="checkbox" name="sdms_allowed_roles[]" value="' . esc_attr( $role_slug ) . '" ' . checked( is_array( $allowed_roles ) && in_array( $role_slug, $allowed_roles ), true, false ) . '> ' . esc_html( $role_name ) . '</label><br>';
            }
        } else {
            echo '<p>' . __( 'No custom roles defined. Please add roles in the SDMS settings.', 'sdms' ) . '</p>';
        }

        // Display user autocomplete field
        echo '<p><strong>' . __( 'Allowed Users', 'sdms' ) . '</strong></p>';
        echo '<div id="sdms-user-search-container">';
        echo '<input type="text" id="sdms-user-search" placeholder="' . esc_attr__( 'Search for users...', 'sdms' ) . '" class="regular-text">';
        echo '</div>';

        // Display selected users
        echo '<ul id="sdms-selected-users">';
        foreach ( $allowed_users as $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user ) {
                echo '<li data-user-id="' . esc_attr( $user_id ) . '">';
                echo esc_html( $user->display_name ) . ' (' . esc_html( $user->user_email ) . ')';
                echo ' <button type="button" class="button sdms-remove-user">&times;</button>';
                echo '<input type="hidden" name="sdms_allowed_users[]" value="' . esc_attr( $user_id ) . '">';
                echo '</li>';
            }
        }
        echo '</ul>';
        echo '<p class="description">' . __( 'Only selected roles and users will have access to this document. If none are selected, the document is public.', 'sdms' ) . '</p>';
    }

    /**
     * Save the access control settings when the post is saved.
     *
     * @param int $post_id The ID of the current post.
     */
    public function save_access_control_settings( $post_id ) {
        // Verify nonce
        if ( ! isset( $_POST['sdms_access_control_nonce'] ) || ! wp_verify_nonce( $_POST['sdms_access_control_nonce'], 'sdms_save_access_control' ) ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sanitize and save data
        $allowed_roles = isset( $_POST['sdms_allowed_roles'] ) ? array_map( 'sanitize_text_field', $_POST['sdms_allowed_roles'] ) : array();
        $allowed_users = isset( $_POST['sdms_allowed_users'] ) ? array_map( 'intval', $_POST['sdms_allowed_users'] ) : array();

        update_post_meta( $post_id, '_sdms_allowed_roles', $allowed_roles );
        update_post_meta( $post_id, '_sdms_allowed_users', $allowed_users );
    }

    /**
     * Handle AJAX request for user search in the Access Control meta box.
     */
    public function handle_user_search_ajax() {
        check_ajax_referer( 'sdms_user_search_nonce', 'nonce' );

        $search_term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';

        $users = get_users( array(
            'search'         => '*' . esc_attr( $search_term ) . '*',
            'search_columns' => array( 'user_login', 'user_nicename', 'user_email', 'display_name' ),
            'number'         => 10,
        ) );

        $results = array();
        foreach ( $users as $user ) {
            $results[] = array(
                'id'   => $user->ID,
                'label'=> $user->display_name . ' (' . $user->user_email . ')',
                'value'=> $user->display_name . ' (' . $user->user_email . ')',
            );
        }

        wp_send_json( $results );
    }

    /**
     * Enqueue admin scripts and styles for the Access Control meta box.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        global $post_type;
        if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'sdms_document' === $post_type ) {
            wp_enqueue_style( 'sdms-admin-styles', SDMS_PLUGIN_URL . 'assets/css/sdms-admin-styles.css' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script( 'sdms-admin-script', SDMS_PLUGIN_URL . 'assets/js/sdms-admin-script.js', array( 'jquery', 'jquery-ui-autocomplete' ), '1.0.0', true );

            // Localize script with necessary data
            wp_localize_script( 'sdms-admin-script', 'sdmsAdmin', array(
                'ajax_url'           => admin_url( 'admin-ajax.php' ),
                'user_search_nonce'  => wp_create_nonce( 'sdms_user_search_nonce' ),
                'remove_label'       => __( 'Remove', 'sdms' ),
                // Other data...
            ) );
        }
    }
}

