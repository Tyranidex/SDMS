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
        // Register settings for languages, template selection, and file type icons
        register_setting( 'sdms_settings_group', 'sdms_languages', array( $this, 'sanitize_languages' ) );
        register_setting( 'sdms_settings_group', 'sdms_template' );
        register_setting( 'sdms_settings_group', 'sdms_file_type_icons', array( $this, 'sanitize_file_type_icons' ) );
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
                    'country'     => sanitize_text_field( $language['country'] ),
                    'flag'        => esc_url_raw( $language['flag'] ),
                    'custom_flag' => isset( $language['custom_flag'] ) ? esc_url_raw( $language['custom_flag'] ) : '',
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
     * Enqueue the icon uploader script for the settings page.
     */
    private function enqueue_icon_uploader_script() {
        wp_enqueue_media();
        wp_enqueue_script( 'sdms-icon-uploader', sdms_PLUGIN_URL . 'assets/js/sdms-icon-uploader.js', array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'sdms-icon-uploader', 'sdmsIconUploader', array(
            'title'  => __( 'Choose Icon', 'sdms' ),
            'button' => __( 'Use this icon', 'sdms' ),
        ) );
    }

    /**
     * Render the settings page content.
     */
    public function render_settings_page() {
        // Get the selected template from options
        $selected_template = get_option( 'sdms_template', 'template-default.php' );

        // Paths to template directories
        $plugin_templates_dir = sdms_PLUGIN_DIR . 'templates/';
        $theme_templates_dir  = get_stylesheet_directory() . '/sdms-templates/';

        // Merge templates from both plugin and theme directories
        $template_files = array_merge(
            glob( $plugin_templates_dir . '*.php' ) ?: array(),
            glob( $theme_templates_dir . '*.php' ) ?: array()
        );

        // Prepare an array of templates for the select dropdown
        $template_options = array();
        foreach ( $template_files as $template_path ) {
            $template_file = basename( $template_path );
            $template_data = get_file_data( $template_path, array( 'Template Name' => 'Template Name' ) );
            $template_name = ! empty( $template_data['Template Name'] ) ? $template_data['Template Name'] : ucwords( str_replace( array( 'template-', '.php', '-' ), array( '', '', ' ' ), $template_file ) );
            $template_options[ $template_file ] = $template_name;
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

                // Load available languages from JSON file
                $json_file = sdms_PLUGIN_DIR . 'languages.json';
                $available_languages = array();
                if ( file_exists( $json_file ) ) {
                    $json_data = file_get_contents( $json_file );
                    $available_languages = json_decode( $json_data, true );
                }
                ?>

                <!-- Add Languages Section -->
                <h2><?php _e( 'Add Languages', 'sdms' ); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Available Languages', 'sdms' ); ?></th>
                        <td>
                            <select id="sdms_language_selector">
                                <?php
                                // Populate the language selector dropdown
                                foreach ( $available_languages as $lang ) {
                                    echo '<option value="' . esc_attr( $lang['code'] ) . '">' . esc_html( $lang['country'] ) . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="button" id="sdms_add_language"><?php _e( 'Add', 'sdms' ); ?></button>
                        </td>
                    </tr>
                </table>

                <!-- Template Settings Section -->
                <h2><?php _e( 'Template Settings', 'sdms' ); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Select Template', 'sdms' ); ?></th>
                        <td>
                            <select name="sdms_template">
                                <?php
                                // Populate the template selection dropdown
                                foreach ( $template_options as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <!-- Added Languages Section -->
                <h2><?php _e( 'Added Languages', 'sdms' ); ?></h2>
                <table class="form-table" id="sdms_languages_table">
                    <?php
                    // Display the list of added languages
                    foreach ( $languages as $code => $language ) {
                        // Ensure 'custom_flag' exists
                        $custom_flag = isset( $language['custom_flag'] ) ? $language['custom_flag'] : '';
                        // Determine the flag URL
                        $flag_url = ! empty( $custom_flag ) ? $custom_flag : $language['flag'];
                        echo '<tr>';
                        echo '<td>';
                        echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['country'] ) . '" style="vertical-align: middle; margin-right: 5px;">';
                        echo esc_html( $language['country'] ) . ' (' . esc_html( $code ) . ')';
                        echo '</td>';
                        echo '<td>';
                        echo '<button type="button" class="button sdms-upload-flag" data-code="' . esc_attr( $code ) . '">' . __( 'Upload Custom Flag', 'sdms' ) . '</button> ';
                        echo '<button type="button" class="button sdms-remove-language" data-code="' . esc_attr( $code ) . '">' . __( 'Remove', 'sdms' ) . '</button>';
                        // Hidden inputs to store language data
                        echo '<input type="hidden" name="sdms_languages[' . esc_attr( $code ) . '][country]" value="' . esc_attr( $language['country'] ) . '">';
                        echo '<input type="hidden" name="sdms_languages[' . esc_attr( $code ) . '][flag]" value="' . esc_url( $language['flag'] ) . '">';
                        echo '<input type="hidden" name="sdms_languages[' . esc_attr( $code ) . '][custom_flag]" value="' . esc_url( $custom_flag ) . '">';
                        echo '</td>';
                        echo '</tr>';
                    }
                    ?>
                </table>

                <!-- File Type Icons Section -->
                <h2><?php _e( 'File Type Icons', 'sdms' ); ?></h2>
                <table class="form-table">
                    <?php
                    // Display the file type icons with options to change them
                    foreach ( $file_types as $key => $label ) {
                        ?>
                        <tr valign="top">
                            <th scope="row"><?php echo esc_html( $label ); ?></th>
                            <td>
                                <?php
                                // Determine the icon URL
                                $icon_url = isset( $file_type_icons[ $key ] ) ? $file_type_icons[ $key ] : sdms_PLUGIN_URL . 'assets/images/icons/' . $key . '.png';
                                ?>
                                <img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $label ); ?>" style="max-width: 50px; max-height: 50px;">
                                <!-- Hidden input to store the icon URL -->
                                <input type="hidden" name="sdms_file_type_icons[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_url( $icon_url ); ?>">
                                <!-- Button to upload a new icon -->
                                <input type="button" class="button sdms-upload-icon-button" data-file-type="<?php echo esc_attr( $key ); ?>" value="<?php _e( 'Change Icon', 'sdms' ); ?>">
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <?php
                // Include JavaScript for media uploader
                $this->enqueue_icon_uploader_script();
                ?>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- JavaScript to handle dynamic functionality on the settings page -->
        <script>
            (function($){
                var availableLanguages = <?php echo json_encode( $available_languages ); ?>;

                // Function to update the language options in the selector
                function updateLanguageOptions() {
                    var addedCodes = [];
                    $('#sdms_languages_table input[name^="sdms_languages"]').each(function() {
                        var code = $(this).attr('name').match(/\[(.*?)\]/)[1];
                        if ($.inArray(code, addedCodes) === -1) {
                            addedCodes.push(code);
                        }
                    });
                    $('#sdms_language_selector option').each(function() {
                        var option = $(this);
                        if ($.inArray(option.val(), addedCodes) !== -1) {
                            option.remove();
                        }
                    });
                }

                // Add language button click event
                $('#sdms_add_language').on('click', function(){
                    var selectedCode = $('#sdms_language_selector').val();
                    var selectedLanguage = availableLanguages.find(function(lang) {
                        return lang.code === selectedCode;
                    });

                    if (selectedLanguage) {
                        // Append new language to the table
                        var newRow = '<tr>' +
                            '<td>' +
                                '<img src="' + selectedLanguage.flag + '" alt="' + selectedLanguage.country + '" style="vertical-align: middle; margin-right: 5px;" class="sdms-flag-image"> ' +
                                selectedLanguage.country + ' (' + selectedCode + ')' +
                            '</td>' +
                            '<td>' +
                                '<button type="button" class="button sdms-upload-flag" data-code="' + selectedCode + '">' + '<?php _e( 'Upload Custom Flag', 'sdms' ); ?>' + '</button> ' +
                                '<button type="button" class="button sdms-remove-language" data-code="' + selectedCode + '">' + '<?php _e( 'Remove', 'sdms' ); ?>' + '</button>' +
                                '<input type="hidden" name="sdms_languages[' + selectedCode + '][country]" value="' + selectedLanguage.country + '">' +
                                '<input type="hidden" name="sdms_languages[' + selectedCode + '][flag]" value="' + selectedLanguage.flag + '">' +
                                '<input type="hidden" name="sdms_languages[' + selectedCode + '][custom_flag]" value="">' +
                            '</td>' +
                        '</tr>';

                        $('#sdms_languages_table').append(newRow);

                        // Remove the language from the dropdown
                        $('#sdms_language_selector option[value="' + selectedCode + '"]').remove();
                    }
                });

                // Remove language button click event
                $(document).on('click', '.sdms-remove-language', function(){
                    var code = $(this).data('code');
                    $(this).closest('tr').remove();
                    // Remove hidden inputs
                    $('input[name^="sdms_languages[' + code + ']"]').remove();
                    // Add back to dropdown
                    var language = availableLanguages.find(function(lang) {
                        return lang.code === code;
                    });
                    if (language) {
                        $('#sdms_language_selector').append('<option value="' + code + '">' + language.country + '</option>');
                    }
                });

                // Upload custom flag button click event
                $(document).on('click', '.sdms-upload-flag', function(e){
                    e.preventDefault();
                    var button = $(this);
                    var code = button.data('code');

                    var frame = wp.media({
                        title: '<?php _e( 'Choose Custom Flag', 'sdms' ); ?>',
                        button: { text: '<?php _e( 'Use this flag', 'sdms' ); ?>' },
                        library: { type: 'image' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        // Update the hidden input with the custom flag URL
                        $('input[name="sdms_languages[' + code + '][custom_flag]"]').val(attachment.url);
                        // Update the flag image displayed
                        button.closest('tr').find('.sdms-flag-image').attr('src', attachment.url);
                    });

                    frame.open();
                });

                // Initialize on page load
                $(document).ready(function() {
                    updateLanguageOptions();
                });
            })(jQuery);
        </script>
        <?php
    }
}
