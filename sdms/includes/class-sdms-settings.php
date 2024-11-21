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

        add_action( 'update_option_sdms_user_roles', array( $this, 'update_user_roles' ), 10, 2 );
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
        register_setting( 'sdms_settings_group', 'sdms_template' );
        register_setting( 'sdms_settings_group', 'sdms_archive_template' );
        register_setting( 'sdms_settings_group', 'sdms_taxonomy_template' );
        register_setting( 'sdms_settings_group', 'sdms_file_type_icons', array( $this, 'sanitize_file_type_icons' ) );
        register_setting( 'sdms_settings_group', 'sdms_flag_icon_type' );
        register_setting( 'sdms_settings_group', 'sdms_user_roles', array( $this, 'sanitize_user_roles' ) );
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

    /**
     * Sanitize the user role input from the settings form.
     *
     * @param array $input The input array to sanitize.
     * @return array The sanitized file type icons array.
     */
    public function sanitize_user_roles( $input ) {
        $sanitized = array();

        if ( is_array( $input ) ) {
            foreach ( $input as $key => $role_data ) {
                // Sanitize slug and name
                $slug = isset( $role_data['slug'] ) ? sanitize_key( $role_data['slug'] ) : '';
                $name = isset( $role_data['name'] ) ? sanitize_text_field( $role_data['name'] ) : '';

                if ( $slug && $name ) {
                    $sanitized[ $slug ] = $name;
                }
            }
        }

        return $sanitized;
    }

    /**
     * Handle updates to user roles when the option is saved.
     *
     * @param mixed $old_value The old value of the option.
     * @param mixed $new_value The new value of the option.
     */
    public function update_user_roles( $old_value, $new_value ) {
        global $wp_roles;

        // Handle roles that have been removed
        $old_slugs = array_keys( $old_value );
        $new_slugs = array_keys( $new_value );
        $roles_to_remove = array_diff( $old_slugs, $new_slugs );

        foreach ( $roles_to_remove as $role_slug ) {
            // Remove the role
            remove_role( $role_slug );

            // Update users who had this role
            $users = get_users( array( 'role' => $role_slug ) );
            foreach ( $users as $user ) {
                $user->remove_role( $role_slug );
            }

            // Update documents that referenced this role
            $this->update_documents_role_slug( $role_slug, '' );
        }

        // Handle roles that have been added or updated
        foreach ( $new_value as $new_slug => $new_name ) {
            if ( isset( $old_value[ $new_slug ] ) ) {
                // Role exists, check if name has changed
                if ( $old_value[ $new_slug ] !== $new_name ) {
                    // Update role name
                    if ( isset( $wp_roles->roles[ $new_slug ] ) ) {
                        $wp_roles->roles[ $new_slug ]['name'] = $new_name;
                        $wp_roles->role_names[ $new_slug ] = $new_name;
                    }
                }
            } else {
                // New role, add it
                add_role( $new_slug, $new_name, array( 'read' => true ) );
            }
        }

        // Handle role slug changes
        $this->handle_role_slug_changes( $old_value, $new_value );
    }

    /**
     * Handle role slug changes by updating users and documents.
     *
     * @param array $old_roles The old roles array.
     * @param array $new_roles The new roles array.
     */
    private function handle_role_slug_changes( $old_roles, $new_roles ) {
        // Map old slugs to new slugs
        $slug_changes = array();

        foreach ( $old_roles as $old_slug => $old_name ) {
            $found = false;
            foreach ( $new_roles as $new_slug => $new_name ) {
                if ( $old_name === $new_name && $old_slug !== $new_slug ) {
                    // Slug has changed for this role
                    $slug_changes[ $old_slug ] = $new_slug;

                    // Update role
                    remove_role( $old_slug );
                    add_role( $new_slug, $new_name, array( 'read' => true ) );

                    // Update users who have the old role
                    $users = get_users( array( 'role' => $old_slug ) );
                    foreach ( $users as $user ) {
                        $user->remove_role( $old_slug );
                        $user->add_role( $new_slug );
                    }

                    // Update documents that reference the old role slug
                    $this->update_documents_role_slug( $old_slug, $new_slug );

                    $found = true;
                    break;
                }
            }
        }
    }

    /**
     * Update documents that reference an old role slug to use the new role slug.
     *
     * @param string $old_slug The old role slug.
     * @param string $new_slug The new role slug.
     */
    private function update_documents_role_slug( $old_slug, $new_slug ) {
        // Query all documents that reference the old role slug
        $args = array(
            'post_type'   => 'sdms_document',
            'post_status' => 'any',
            'meta_query'  => array(
                array(
                    'key'     => '_sdms_allowed_roles',
                    'value'   => $old_slug,
                    'compare' => 'LIKE',
                ),
            ),
            'fields'      => 'ids',
            'nopaging'    => true,
        );

        $posts = get_posts( $args );

        foreach ( $posts as $post_id ) {
            $allowed_roles = get_post_meta( $post_id, '_sdms_allowed_roles', true );
            if ( is_array( $allowed_roles ) ) {
                $key = array_search( $old_slug, $allowed_roles );
                if ( false !== $key ) {
                    if ( $new_slug ) {
                        // Replace old slug with new slug
                        $allowed_roles[ $key ] = $new_slug;
                    } else {
                        // Remove the role from the array
                        unset( $allowed_roles[ $key ] );
                    }
                    // Update the post meta
                    update_post_meta( $post_id, '_sdms_allowed_roles', array_values( $allowed_roles ) );
                }
            }
        }
    }
}
