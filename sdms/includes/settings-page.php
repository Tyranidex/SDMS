<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings Page Template
 *
 * This template renders the settings page for the SDMS plugin.
 */
?>

<div class="wrap">
    <h1><?php esc_html_e( 'SDMS Settings', 'sdms' ); ?></h1>
    <form method="post" action="options.php">
        <?php
        // Output settings fields and sections
        settings_fields( 'sdms_settings_group' );

        // Get configured languages
        $languages = get_option( 'sdms_languages', array() );
        if ( ! is_array( $languages ) ) {
            $languages = array();
        }

        // Get existing user roles
        $sdms_user_roles = get_option( 'sdms_user_roles', array() );

        // Get available languages from JSON file
        $available_languages = sdms_get_available_languages();

        // Retrieve selected templates from options
        $selected_template          = get_option( 'sdms_template', 'single-template-default.php' );
        $selected_archive_template  = get_option( 'sdms_archive_template', 'archive-template-default.php' );
        $selected_taxonomy_template = get_option( 'sdms_taxonomy_template', 'taxonomy-template-default.php' );

        // Paths to template directories
        $plugin_templates_dir = SDMS_PLUGIN_DIR . 'templates/';
        $theme_templates_dir  = get_stylesheet_directory() . '/sdms-templates/';

        // Merge templates from plugin and theme directories
        $plugin_template_files = glob( $plugin_templates_dir . '*.php' ) ?: array();
        $theme_template_files  = glob( $theme_templates_dir . '*.php' ) ?: array();
        $template_files        = array_merge( $plugin_template_files, $theme_template_files );

        // Prepare arrays for single post and archive templates
        $template_options         = array();
        $archive_template_options = array();

        // Retrieve selected flag icon type
        $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );

        // If 'custom' is selected but the directory doesn't exist, revert to 'squared'
        if ( $selected_flag_icon_type === 'custom' ) {
            $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
            if ( ! ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) ) {
                $selected_flag_icon_type = 'squared';
            }
        }

        // Available flag icon types
        $flag_icon_types = array(
            'circled' => __( 'Circled', 'sdms' ),
            'rounded' => __( 'Rounded', 'sdms' ),
            'squared' => __( 'Squared', 'sdms' ),
        );

        // Check if custom flags directory exists in the theme
        $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
        if ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) {
            $flag_icon_types['custom'] = __( 'My Custom Flags', 'sdms' );
        }

        foreach ( $template_files as $template_path ) {
            $template_file = basename( $template_path );
            $template_data = get_file_data( $template_path, array( 'Template Name' => 'Template Name' ) );
            $template_name = ! empty( $template_data['Template Name'] ) ? $template_data['Template Name'] : ucwords( str_replace( array( 'template-', '.php', '-' ), array( '', '', ' ' ), $template_file ) );

            if ( strpos( $template_file, 'archive-' ) === 0 ) {
                // Archive template
                $archive_template_options[ $template_file ] = $template_name;
            } elseif ( strpos( $template_file, 'single-' ) === 0 ) {
                // Single post template
                $template_options[ $template_file ] = $template_name;
            }
        }

        // Get existing file type icons
        $file_types = array(
            'pdf'   => __( 'PDF', 'sdms' ),
            'word'  => __( 'Word', 'sdms' ),
            'excel' => __( 'Excel', 'sdms' ),
            'image' => __( 'Image', 'sdms' ),
            'video' => __( 'Video', 'sdms' ),
            'psd'   => __( 'Photoshop', 'sdms' ),
            'ai'    => __( 'Illustrator', 'sdms' ),
        );

        $file_type_icons = get_option( 'sdms_file_type_icons', array() );

        // Define default file type icons
        $default_file_type_icons = array();
        foreach ( array_keys( $file_types ) as $key ) {
            $default_file_type_icons[ $key ] = SDMS_PLUGIN_URL . 'assets/images/icons/' . $key . '.png';
        }
        ?>

        <!-- Navigation Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#tab-languages" class="nav-tab nav-tab-active"><?php esc_html_e( 'Languages', 'sdms' ); ?></a>
            <a href="#tab-icons" class="nav-tab"><?php esc_html_e( 'Icons', 'sdms' ); ?></a>
            <a href="#tab-templates" class="nav-tab"><?php esc_html_e( 'Templates', 'sdms' ); ?></a>
            <a href="#tab-user-roles" class="nav-tab"><?php esc_html_e( 'User Roles', 'sdms' ); ?></a>
        </h2>

        <!-- Languages Tab Content -->
        <div id="tab-languages" class="tab-content">
            <p><?php esc_html_e( 'Add or delete languages for Documents', 'sdms' ); ?></p>

            <!-- Add Languages Section -->
            <h2><?php esc_html_e( 'Add Languages', 'sdms' ); ?></h2>
            <div class="sdms-add-language">
                <label for="sdms_language_selector"><?php esc_html_e( 'Available Languages:', 'sdms' ); ?></label>
                <select id="sdms_language_selector" class="regular-text">
                    <option value=""><?php esc_html_e( '-- Select a language --', 'sdms' ); ?></option>
                    <?php
                    // Populate the language selector dropdown
                    foreach ( $available_languages as $lang ) {
                        // Skip already added languages
                        if ( array_key_exists( $lang['code'], $languages ) ) {
                            continue;
                        }
                        echo '<option value="' . esc_attr( $lang['code'] ) . '">' . esc_html( $lang['lang'] ) . '</option>';
                    }
                    ?>
                </select>
                <button type="button" class="button" id="sdms_add_language"><?php esc_html_e( 'Add', 'sdms' ); ?></button>
            </div>

            <!-- Added Languages Section -->
            <h2><?php esc_html_e( 'Added Languages', 'sdms' ); ?></h2>
            <table class="wp-list-table widefat fixed striped" id="sdms_languages_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Flag', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Language', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Language Code', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'sdms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display the list of added languages
                    foreach ( $languages as $code => $language ) {
                        $flag_url = sdms_get_flag_url( $code );
                        echo '<tr>';
                        // Flag column
                        echo '<td>';
                        echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" class="sdms-flag-image">';
                        echo '</td>';
                        // Language name column
                        echo '<td>';
                        echo esc_html( $language['lang'] );
                        echo '</td>';
                        // Language code column
                        echo '<td>';
                        echo esc_html( $code );
                        echo '</td>';
                        // Actions column
                        echo '<td>';
                        echo '<button type="button" class="button sdms-remove-language" data-code="' . esc_attr( $code ) . '">' . __( 'Remove', 'sdms' ) . '</button>';
                        // Hidden inputs to store language data
                        echo '<input type="hidden" name="sdms_languages[' . esc_attr( $code ) . '][lang]" value="' . esc_attr( $language['lang'] ) . '">';
                        echo '<input type="hidden" name="sdms_languages[' . esc_attr( $code ) . '][flag]" value="' . esc_url( $language['flag'] ) . '">';
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Icons Tab Content -->
        <div id="tab-icons" class="tab-content" style="display: none;">
            <p><?php esc_html_e( 'Manage file type icons. You can change existing icons or add new ones.', 'sdms' ); ?></p>

            <!-- Flag Icon Type Setting -->
            <h2><?php esc_html_e( 'Flag Icon Settings', 'sdms' ); ?></h2>
            <div class="sdms-change-flags">
                <label for="sdms_flags_selector"><?php esc_html_e( 'Select Flag Icon Type', 'sdms' ); ?></label>
                <select id="sdms_flags_selector" name="sdms_flag_icon_type" class="regular-text">
                    <option value=""><?php esc_html_e( '-- Select Icon Type --', 'sdms' ); ?></option>
                    <?php
                        foreach ( $flag_icon_types as $value => $label ) {
                            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_flag_icon_type, $value, false ) . '>' . esc_html( $label ) . '</option>';
                        }
                    ?>
                </select>
            </div>

            <!-- File Type Icons Section -->
            <h2><?php esc_html_e( 'File Type Icons', 'sdms' ); ?></h2>
            <div class="sdms-file-type-icons">
                <?php
                // Display each file type with its icon and buttons
                foreach ( $file_types as $key => $label ) {
                    // Determine the current icon URL
                    $icon_url = isset( $file_type_icons[ $key ] ) ? $file_type_icons[ $key ] : $default_file_type_icons[ $key ];
                    // Get the default icon URL
                    $default_url = $default_file_type_icons[ $key ];
                    // Check if the current icon is custom
                    $is_custom = ( $icon_url !== $default_url );
                    ?>
                    <div class="sdms-file-type-icon">
                        <div class="sdms-file-type-label">
                            <label><?php echo esc_html( $label ); ?></label>
                        </div>
                        <div class="sdms-file-type-image">
                            <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $label ); ?>" class="sdms-file-icon-image">
                        </div>
                        <div class="sdms-file-type-action">
                            <!-- Hidden input to store the icon URL -->
                            <input type="hidden" name="sdms_file_type_icons[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_url( $icon_url ); ?>">
                            <!-- Button to upload a new icon -->
                            <button type="button" class="button sdms-upload-icon-button" data-file-type="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Change Icon', 'sdms' ); ?></button>
                            <?php if ( $is_custom ) : ?>
                                <!-- Button to reset to default icon -->
                                <button type="button" class="button sdms-reset-icon-button" data-file-type="<?php echo esc_attr( $key ); ?>" data-default-url="<?php echo esc_url( $default_url ); ?>"><?php esc_html_e( 'Reset', 'sdms' ); ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Templates Tab Content -->
        <div id="tab-templates" class="tab-content" style="display: none;">
            <p><?php esc_html_e( 'Select templates to use for individual posts, archives, and taxonomies.', 'sdms' ); ?></p>

            <h2><?php esc_html_e( 'Template Settings', 'sdms' ); ?></h2>
            <table class="wp-list-table widefat fixed striped sdms-templates-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Template Type', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Select Template', 'sdms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e( 'Single Post Template', 'sdms' ); ?></td>
                        <td>
                            <select name="sdms_template" id="sdms_template" class="regular-text">
                                <?php
                                // Populate the template selection dropdown for single posts
                                foreach ( $template_options as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Archive Template', 'sdms' ); ?></td>
                        <td>
                            <select name="sdms_archive_template" id="sdms_archive_template" class="regular-text">
                                <?php
                                // Populate the template selection dropdown for archives
                                foreach ( $archive_template_options as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_archive_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e( 'Taxonomy Template', 'sdms' ); ?></td>
                        <td>
                            <select name="sdms_taxonomy_template" id="sdms_taxonomy_template" class="regular-text">
                                <?php
                                // Populate the template selection dropdown for taxonomy archives
                                $taxonomy_templates = array();

                                foreach ( $template_files as $template_path ) {
                                    $template_file = basename( $template_path );
                                    if ( strpos( $template_file, 'taxonomy-' ) === 0 ) {
                                        $template_data = get_file_data( $template_path, array( 'Template Name' => 'Template Name' ) );
                                        $template_name = ! empty( $template_data['Template Name'] ) ? $template_data['Template Name'] : ucwords( str_replace( array( 'template-', '.php', '-' ), array( '', '', ' ' ), $template_file ) );
                                        $taxonomy_templates[ $template_file ] = $template_name;
                                    }
                                }

                                foreach ( $taxonomy_templates as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_taxonomy_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select a template for taxonomy archives. You can create custom templates in your theme\'s sdms-templates folder.', 'sdms' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- User Roles Tab Content -->
        <div id="tab-user-roles" class="tab-content" style="display: none;">
            <h2><?php esc_html_e( 'User Roles', 'sdms' ); ?></h2>
            <p><?php esc_html_e( 'Add, edit, or remove custom user roles for document access control.', 'sdms' ); ?></p>

            <table class="wp-list-table widefat fixed striped" id="sdms_user_roles_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Role Slug', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Role Name', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'sdms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $sdms_user_roles as $slug => $name ) {
                        echo '<tr>';
                        echo '<td><input type="text" name="sdms_user_roles[' . esc_attr( $slug ) . '][slug]" value="' . esc_attr( $slug ) . '" class="regular-text sdms-role-slug"></td>';
                        echo '<td><input type="text" name="sdms_user_roles[' . esc_attr( $slug ) . '][name]" value="' . esc_attr( $name ) . '" class="regular-text"></td>';
                        echo '<td><button type="button" class="button sdms-remove-role" data-role="' . esc_attr( $slug ) . '">' . esc_html__( 'Remove', 'sdms' ) . '</button></td>';
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
            <h3><?php esc_html_e( 'Add New Role', 'sdms' ); ?></h3>
            <div class="sdms-add-role">
                <input type="text" id="sdms_new_role_slug" placeholder="<?php esc_attr_e( 'Role Slug (lowercase, no spaces)', 'sdms' ); ?>" class="regular-text">
                <input type="text" id="sdms_new_role_name" placeholder="<?php esc_attr_e( 'Role Name', 'sdms' ); ?>" class="regular-text">
                <button type="button" class="button" id="sdms_add_role"><?php esc_html_e( 'Add Role', 'sdms' ); ?></button>
            </div>
            <p class="description"><?php esc_html_e( 'Note: Changing the slug of an existing role will update all documents and users associated with that role.', 'sdms' ); ?></p>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
