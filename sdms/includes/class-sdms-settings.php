<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class sdms_Settings
 *
 * Handles the settings page for the SDMS plugin, allowing administrators
 * to configure languages, templates, and file type icons.
 */
class sdms_Settings {

    public function __construct() {
        // Add settings page to the admin menu
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );

        // Register settings
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // Enqueue scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
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
        register_setting( 'sdms_settings_group', 'sdms_taxonomy_template', array( $this, 'sanitize_taxonomy_template' ) );
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
     * Sanitize the taxonomy template input from the settings form.
     *
     * @param string $input The input string to sanitize.
     * @return string The sanitized template filename.
     */
    public function sanitize_taxonomy_template( $input ) {
        // List of allowed templates (plugin + theme)
        $allowed_templates = array();

        // Plugin templates
        $plugin_taxonomy_templates_dir = sdms_PLUGIN_DIR . 'templates/';
        if ( file_exists( $plugin_taxonomy_templates_dir ) && is_dir( $plugin_taxonomy_templates_dir ) ) {
            $plugin_taxonomy_templates = array_diff( scandir( $plugin_taxonomy_templates_dir ), array( '.', '..' ) );
            $allowed_templates = array_merge( $allowed_templates, $plugin_taxonomy_templates );
        }

        // Theme templates
        $theme_taxonomy_templates_dir = get_stylesheet_directory() . '/sdms-templates/';
        if ( file_exists( $theme_taxonomy_templates_dir ) && is_dir( $theme_taxonomy_templates_dir ) ) {
            $theme_taxonomy_templates = array_diff( scandir( $theme_taxonomy_templates_dir ), array( '.', '..' ) );
            $allowed_templates = array_merge( $allowed_templates, $theme_taxonomy_templates );
        }

        // Add default option
        $allowed_templates[] = 'taxonomy-template-default.php';

        // Verify if the selected template is allowed
        if ( in_array( $input, $allowed_templates ) ) {
            return sanitize_file_name( $input );
        }

        // Return default template if not valid
        return 'taxonomy-template-default.php';
    }

    /**
     * Enqueue admin assets (scripts and styles).
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_admin_assets( $hook ) {
        global $post_type;

        // Enqueue only on the post edit screen for 'sdms_document' or on the SDMS settings page
        if ( ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'sdms_document' === $post_type ) || 'settings_page_sdms-settings' === $hook ) {
            wp_enqueue_style( 'sdms-admin-styles', sdms_PLUGIN_URL . 'assets/css/sdms-admin-styles.css', array(), '1.0.0' );

            // Enqueue media scripts and custom script on the settings page
            if ( 'settings_page_sdms-settings' === $hook ) {
                wp_enqueue_media();
                
                // Define default file type icons
                $file_types = array(
                    'pdf'   => sdms_PLUGIN_URL . 'assets/images/icons/pdf.png',
                    'word'  => sdms_PLUGIN_URL . 'assets/images/icons/word.png',
                    'excel' => sdms_PLUGIN_URL . 'assets/images/icons/excel.png',
                    'image' => sdms_PLUGIN_URL . 'assets/images/icons/image.png',
                    'video' => sdms_PLUGIN_URL . 'assets/images/icons/video.png',
                    'psd'   => sdms_PLUGIN_URL . 'assets/images/icons/psd.png',
                    'ai'    => sdms_PLUGIN_URL . 'assets/images/icons/ai.png',
                );

                wp_enqueue_script( 'sdms-icon-uploader', sdms_PLUGIN_URL . 'assets/js/sdms-icon-uploader.js', array( 'jquery' ), '1.0.0', true );
                wp_localize_script( 'sdms-icon-uploader', 'sdmsIconUploader', array(
                    'title'            => __( 'Choose Icon', 'sdms' ),
                    'button'           => __( 'Use this icon', 'sdms' ),
                    'default_icons'    => $file_types,
                    'remove_label'     => __( 'Remove', 'sdms' ),
                    'reset_label'      => __( 'Reset', 'sdms' ),
                    'add_language_alert' => __( 'Veuillez sélectionner une langue à ajouter.', 'sdms' ),
                ) );
            }
        }
    }

    /**
     * Load available languages from languages.json.
     *
     * @return array Available languages.
     */
    public function get_available_languages() {
        $json_file = sdms_LANGUAGES_FILE;
        if ( file_exists( $json_file ) ) {
            $json_data = file_get_contents( $json_file );
            if ( $json_data === false ) {
                error_log( 'Erreur lors de la lecture du fichier languages.json' );
                return array();
            }
            $languages = json_decode( $json_data, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                // Add the 'flag' field to each language
                $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );
                foreach ( $languages as &$language ) {
                    $code = $language['code'];
                    $flag_path = sdms_PLUGIN_URL . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                    $language['flag'] = $flag_path;
                }
                return $languages;
            } else {
                error_log( 'Erreur JSON : ' . json_last_error_msg() );
                return array();
            }
        } else {
            error_log( 'Le fichier languages.json est introuvable : ' . $json_file );
            return array();
        }
    }

    /**
     * Render the settings page content.
     */
    public function render_settings_page() {
        // Retrieve selected templates from options
        $selected_template          = get_option( 'sdms_template', 'single-template-default.php' );
        $selected_archive_template  = get_option( 'sdms_archive_template', 'archive-template-default.php' );
        $selected_taxonomy_template = get_option( 'sdms_taxonomy_template', 'taxonomy-template-default.php' );

        // Paths to template directories
        $plugin_templates_dir = sdms_PLUGIN_DIR . 'templates/';
        $theme_templates_dir  = get_stylesheet_directory() . '/sdms-templates/';

        // Merge templates from plugin and theme directories
        $plugin_template_files = glob( $plugin_templates_dir . '*.php' ) ?: array();
        $theme_template_files  = glob( $theme_templates_dir . '*.php' ) ?: array();
        $template_files        = array_merge( $plugin_template_files, $theme_template_files );

        // Prepare arrays for single post and archive templates
        $template_options        = array();
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
        $file_types       = array(
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
            $default_file_type_icons[ $key ] = sdms_PLUGIN_URL . 'assets/images/icons/' . $key . '.png';
        }

        ?>
        <div class="wrap">
            <h1><?php _e( 'SDMS Settings', 'sdms' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                // Output settings fields and sections
                settings_fields( 'sdms_settings_group' );

                // Get configured languages
                $languages = get_option( 'sdms_languages', array() );
                if ( ! is_array( $languages ) ) {
                    $languages = array();
                }

                // Get available languages from JSON file
                $available_languages = $this->get_available_languages();
                ?>

                <!-- Navigation Tabs -->
                <h2 class="nav-tab-wrapper">
                    <a href="#tab-languages" class="nav-tab nav-tab-active"><?php _e( 'Langues', 'sdms' ); ?></a>
                    <a href="#tab-icons" class="nav-tab"><?php _e( 'Icônes', 'sdms' ); ?></a>
                    <a href="#tab-templates" class="nav-tab"><?php _e( 'Templates', 'sdms' ); ?></a>
                </h2>

                <!-- Languages Tab Content -->
                <div id="tab-languages" class="tab-content">
                    <p><?php _e( 'Add or delete languages for Documents', 'sdms' ); ?></p>

                    <!-- Add Languages Section -->
                    <h2><?php _e( 'Add Languages', 'sdms' ); ?></h2>
                    <div class="sdms-add-language">
                        <label for="sdms_language_selector"><?php _e( 'Available Languages:', 'sdms' ); ?></label>
                        <select id="sdms_language_selector" class="regular-text">
                            <option value=""><?php _e( '-- Sélectionnez une langue --', 'sdms' ); ?></option>
                            <?php
                            // Populate the language selector dropdown
                            foreach ( $available_languages as $lang ) {
                                echo '<option value="' . esc_attr( $lang['code'] ) . '">' . esc_html( $lang['lang'] ) . '</option>';
                            }
                            ?>
                        </select>
                        <button type="button" class="button" id="sdms_add_language"><?php _e( 'Add', 'sdms' ); ?></button>
                    </div>

                    <!-- Added Languages Section -->
                    <h2><?php _e( 'Added Languages', 'sdms' ); ?></h2>
                    <table class="wp-list-table widefat fixed striped" id="sdms_languages_table">
                        <thead>
                            <tr>
                                <th><?php _e( 'Flag', 'sdms' ); ?></th>
                                <th><?php _e( 'Language', 'sdms' ); ?></th>
                                <th><?php _e( 'Language Code', 'sdms' ); ?></th>
                                <th><?php _e( 'Actions', 'sdms' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display the list of added languages
                            foreach ( $languages as $code => $language ) {
                                // Determine the flag icon path
                                if ( $selected_flag_icon_type === 'custom' ) {
                                    // Custom flag in theme
                                    $flag_file = get_stylesheet_directory() . '/sdms-flags/' . $code . '.png';
                                    $flag_url  = get_stylesheet_directory_uri() . '/sdms-flags/' . $code . '.png';
                                    if ( ! file_exists( $flag_file ) ) {
                                        // Use default flag if custom flag doesn't exist
                                        $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                                    }
                                } else {
                                    // Plugin flag
                                    $flag_file = sdms_PLUGIN_DIR . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                                    $flag_url  = sdms_PLUGIN_URL . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                                    if ( ! file_exists( $flag_file ) ) {
                                        // Use default flag if plugin flag doesn't exist
                                        $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                                    }
                                }
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
                    <p><?php _e( 'Manage file type icons. You can change existing icons or add new ones.', 'sdms' ); ?></p>

                    <!-- Flag Icon Type Setting -->
                    <h2><?php _e( 'Flag Icon Settings', 'sdms' ); ?></h2>
                    <div class="sdms-change-flags">
                        <label for="sdms_flags_selector"><?php _e( 'Select Flag Icon Type', 'sdms' ); ?></label>
                        <select id="sdms_flags_selector" name="sdms_flag_icon_type" class="regular-text">
                            <option value=""><?php _e( '-- Select Icon Type --', 'sdms' ); ?></option>
                            <?php
                                foreach ( $flag_icon_types as $value => $label ) {
                                    echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_flag_icon_type, $value, false ) . '>' . esc_html( $label ) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <!-- File Type Icons Section -->
                    <h2><?php _e( 'File Type Icons', 'sdms' ); ?></h2>
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
                                    <button type="button" class="button sdms-upload-icon-button" data-file-type="<?php echo esc_attr( $key ); ?>"><?php _e( 'Change Icon', 'sdms' ); ?></button>
                                    <?php if ( $is_custom ) : ?>
                                        <!-- Button to reset to default icon -->
                                        <button type="button" class="button sdms-reset-icon-button" data-file-type="<?php echo esc_attr( $key ); ?>" data-default-url="<?php echo esc_url( $default_url ); ?>"><?php _e( 'Reset', 'sdms' ); ?></button>
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
                    <p><?php _e( 'Select templates to use for individual posts, archives, and taxonomies.', 'sdms' ); ?></p>

                    <h2><?php _e( 'Template Settings', 'sdms' ); ?></h2>
                    <table class="wp-list-table widefat fixed striped sdms-templates-table">
                        <thead>
                            <tr>
                                <th><?php _e( 'Template Type', 'sdms' ); ?></th>
                                <th><?php _e( 'Select Template', 'sdms' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php _e( 'Single Post Template', 'sdms' ); ?></td>
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
                                <td><?php _e( 'Archive Template', 'sdms' ); ?></td>
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
                                <td><?php _e( 'Taxonomy Template', 'sdms' ); ?></td>
                                <td>
                                    <select name="sdms_taxonomy_template" id="sdms_taxonomy_template" class="regular-text">
                                        <?php
                                        // List available taxonomy templates from plugin
                                        $plugin_taxonomy_templates_dir = sdms_PLUGIN_DIR . 'templates/';
                                        $plugin_taxonomy_templates = array_diff( scandir( $plugin_taxonomy_templates_dir ), array( '.', '..' ) );

                                        // List available taxonomy templates from theme
                                        $theme_taxonomy_templates_dir = get_stylesheet_directory() . '/sdms-templates/';
                                        if ( file_exists( $theme_taxonomy_templates_dir ) && is_dir( $theme_taxonomy_templates_dir ) ) {
                                            $theme_taxonomy_templates = array_diff( scandir( $theme_taxonomy_templates_dir ), array( '.', '..' ) );
                                        } else {
                                            $theme_taxonomy_templates = array();
                                        }

                                        // Merge templates from plugin and theme
                                        $taxonomy_templates = array_merge( $plugin_taxonomy_templates, $theme_taxonomy_templates );

                                        // Default option
                                        echo '<option value="taxonomy-template-default.php" ' . selected( $selected_taxonomy_template, 'taxonomy-template-default.php', false ) . '>' . __( 'Default Taxonomy Template', 'sdms' ) . '</option>';

                                        // Add other taxonomy templates
                                        foreach ( $taxonomy_templates as $template ) {
                                            if ( $template === 'taxonomy-template-default.php' ) {
                                                continue;
                                            }
                                            if ( strpos( $template, 'taxonomy-' ) !== 0 ) {
                                                continue;
                                            }
                                            echo '<option value="' . esc_attr( $template ) . '" ' . selected( $selected_taxonomy_template, $template, false ) . '>' . esc_html( $template ) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php _e( 'Select a template for taxonomy archives. You can create custom templates in your theme\'s sdms-templates folder.', 'sdms' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

}
