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

        // Register Javascript
        add_action( 'admin_init', array( $this, 'enqueue_icon_uploader_script' ) );

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
        // Enregistrer les paramètres pour les langues, la sélection des templates et les icônes de types de fichiers
        register_setting( 'sdms_settings_group', 'sdms_languages', array( $this, 'sanitize_languages' ) );
        register_setting( 'sdms_settings_group', 'sdms_template' ); // Pour les templates de post individuel
        register_setting( 'sdms_settings_group', 'sdms_archive_template' ); // Pour les templates d'archive
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
     * Enqueue the icon uploader script for the settings page.
     */
    public function enqueue_icon_uploader_script() {
        wp_enqueue_media();
        wp_enqueue_script( 'sdms-icon-uploader', sdms_PLUGIN_URL . 'assets/js/sdms-icon-uploader.js', array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'sdms-icon-uploader', 'sdmsIconUploader', array(
            'title'  => __( 'Choose Icon', 'sdms' ),
            'button' => __( 'Use this icon', 'sdms' ),
        ) );
    }

    /**
     * Charger les langues depuis le fichier languages.json
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
                // Ajouter le champ 'flag' à chaque langue
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
     * Sanitize the taxonomy template input from the settings form.
     *
     * @param string $input The input string to sanitize.
     * @return string The sanitized template filename.
     */
    public function sanitize_taxonomy_template( $input ) {
        // Liste des templates autorisés (plugin + thème)
        $allowed_templates = array();

        // Templates du plugin
        $plugin_taxonomy_templates_dir = sdms_PLUGIN_DIR . 'templates/';
        if ( file_exists( $plugin_taxonomy_templates_dir ) && is_dir( $plugin_taxonomy_templates_dir ) ) {
            $plugin_taxonomy_templates = array_diff( scandir( $plugin_taxonomy_templates_dir ), array( '.', '..' ) );
            $allowed_templates = array_merge( $allowed_templates, $plugin_taxonomy_templates );
        }

        // Templates du thème
        $theme_taxonomy_templates_dir = get_stylesheet_directory() . '/sdms-templates/';
        if ( file_exists( $theme_taxonomy_templates_dir ) && is_dir( $theme_taxonomy_templates_dir ) ) {
            $theme_taxonomy_templates = array_diff( scandir( $theme_taxonomy_templates_dir ), array( '.', '..' ) );
            $allowed_templates = array_merge( $allowed_templates, $theme_taxonomy_templates );
        }

        // Ajoutez l'option par défaut
        $allowed_templates[] = 'taxonomy-template-default.php';

        // Vérifier si le template sélectionné est dans la liste autorisée
        if ( in_array( $input, $allowed_templates ) ) {
            return sanitize_file_name( $input );
        }

        // Retourner le template par défaut si non valide
        return 'taxonomy-template-default.php';
    }

    /**
     * Render the settings page content.
     */
    public function render_settings_page() {
        // Récupérer les templates sélectionnés dans les options
        $selected_template = get_option( 'sdms_template', 'single-template-default.php' );
        $selected_archive_template = get_option( 'sdms_archive_template', 'archive-template-default.php' );
        $selected_taxonomy_template = get_option( 'sdms_taxonomy_template', 'taxonomy-template-default.php' );

        // Chemins vers les répertoires de templates
        $plugin_templates_dir = sdms_PLUGIN_DIR . 'templates/';
        $theme_templates_dir  = get_stylesheet_directory() . '/sdms-templates/';

        // Fusionner les templates des répertoires du plugin et du thème
        $plugin_template_files = glob( $plugin_templates_dir . '*.php' ) ?: array();
        $theme_template_files  = glob( $theme_templates_dir . '*.php' ) ?: array();
        $template_files = array_merge( $plugin_template_files, $theme_template_files );

        // Préparer des tableaux pour les templates de posts individuels et d'archives
        $template_options = array();
        $archive_template_options = array();

        // Récupérer le type d'icône de drapeau sélectionné
        $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );

        // Si 'custom' est sélectionné mais que le dossier n'existe pas, revenir à 'squared'
        if ( $selected_flag_icon_type === 'custom' ) {
            $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
            if ( ! ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) ) {
                $selected_flag_icon_type = 'squared';
            }
        }

        // Options de types d'icônes disponibles
        $flag_icon_types = array(
            'circled' => __( 'Circled', 'sdms' ),
            'rounded' => __( 'Rounded', 'sdms' ),
            'squared' => __( 'Squared', 'sdms' ),
        );

        // Vérifier si le dossier des drapeaux personnalisés existe dans le thème
        $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
        if ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) {
            $flag_icon_types['custom'] = __( 'My Custom Flags', 'sdms' );
        }

        foreach ( $template_files as $template_path ) {
            $template_file = basename( $template_path );
            $template_data = get_file_data( $template_path, array( 'Template Name' => 'Template Name' ) );
            $template_name = ! empty( $template_data['Template Name'] ) ? $template_data['Template Name'] : ucwords( str_replace( array( 'template-', '.php', '-' ), array( '', '', ' ' ), $template_file ) );

            if ( strpos( $template_file, 'archive-' ) === 0 ) {
                // C'est un template d'archive
                $archive_template_options[ $template_file ] = $template_name;
            } elseif ( strpos( $template_file, 'single-' ) === 0 ) {
                // C'est un template de post individuel
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

                // Récupérer les langues disponibles depuis le fichier JSON
                $available_languages = $this->get_available_languages();
                ?>

                <!-- Navigation des Onglets -->
                <h2 class="nav-tab-wrapper">
                    <a href="#tab-languages" class="nav-tab nav-tab-active"><?php _e( 'Langues', 'sdms' ); ?></a>
                    <a href="#tab-icons" class="nav-tab"><?php _e( 'Icônes', 'sdms' ); ?></a>
                    <a href="#tab-templates" class="nav-tab"><?php _e( 'Templates', 'sdms' ); ?></a>
                </h2>

                <!-- Langues Tab Content -->
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
                                echo '<tr>';
                                // Colonne du drapeau
                                echo '<td>';
                                echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" class="sdms-flag-image">';
                                echo '</td>';
                                // Colonne du nom de la langue
                                echo '<td>';
                                echo esc_html( $language['lang'] );
                                echo '</td>';
                                // Colonne du code de langue
                                echo '<td>';
                                echo esc_html( $code );
                                echo '</td>';
                                // Colonne des actions
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

                <!-- Icônes Tab Content -->
                <div id="tab-icons" class="tab-content" style="display: none;">
                    <p><?php _e( 'Gérez les icônes associées aux types de fichiers. Vous pouvez changer les icônes existantes ou en ajouter de nouvelles.', 'sdms' ); ?></p>

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

                    <h2><?php _e( 'File Type Icons', 'sdms' ); ?></h2>
                    <div class="sdms-file-type-icons">
                        <?php
                        // Afficher chaque type de fichier avec son icône et le bouton pour changer
                        foreach ( $file_types as $key => $label ) {
                            // Déterminer l'URL de l'icône
                            $icon_url = isset( $file_type_icons[ $key ] ) ? $file_type_icons[ $key ] : sdms_PLUGIN_URL . 'assets/images/icons/' . $key . '.png';
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
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <!-- Templates Tab Content -->
                <div id="tab-templates" class="tab-content" style="display: none;">
                    <p><?php _e( 'Sélectionnez les templates à utiliser pour les posts individuels, les archives et les taxonomies.', 'sdms' ); ?></p>

                    <h2><?php _e( 'Template Settings', 'sdms' ); ?></h2>
                    <div class="sdms-template-settings">
                        <div class="sdms-template-field">
                            <label for="sdms_template"><?php _e( 'Select Single Post Template:', 'sdms' ); ?></label>
                            <select name="sdms_template" id="sdms_template" class="regular-text">
                                <?php
                                // Populate the template selection dropdown for single posts
                                foreach ( $template_options as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="sdms-template-field">
                            <label for="sdms_archive_template"><?php _e( 'Select Archive Template:', 'sdms' ); ?></label>
                            <select name="sdms_archive_template" id="sdms_archive_template" class="regular-text">
                                <?php
                                // Populate the template selection dropdown for archives
                                foreach ( $archive_template_options as $file => $name ) {
                                    echo '<option value="' . esc_attr( $file ) . '" ' . selected( $selected_archive_template, $file, false ) . '>' . esc_html( $name ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="sdms-template-field">
                            <label for="sdms_taxonomy_template"><?php _e( 'Select Taxonomy Template:', 'sdms' ); ?></label>
                            <select name="sdms_taxonomy_template" id="sdms_taxonomy_template" class="regular-text">
                                <?php
                                // Liste des templates disponibles dans le plugin
                                $plugin_taxonomy_templates_dir = sdms_PLUGIN_DIR . 'templates/';
                                $plugin_taxonomy_templates = array_diff( scandir( $plugin_taxonomy_templates_dir ), array( '.', '..' ) );

                                // Liste des templates disponibles dans le thème
                                $theme_taxonomy_templates_dir = get_stylesheet_directory() . '/sdms-templates/';
                                if ( file_exists( $theme_taxonomy_templates_dir ) && is_dir( $theme_taxonomy_templates_dir ) ) {
                                    $theme_taxonomy_templates = array_diff( scandir( $theme_taxonomy_templates_dir ), array( '.', '..' ) );
                                } else {
                                    $theme_taxonomy_templates = array();
                                }

                                // Fusionner les templates du plugin et du thème
                                $taxonomy_templates = array_merge( $plugin_taxonomy_templates, $theme_taxonomy_templates );

                                // Option par défaut avec sélection si nécessaire
                                echo '<option value="taxonomy-template-default.php" ' . selected( $selected_taxonomy_template, 'taxonomy-template-default.php', false ) . '>' . __( 'Default Taxonomy Template', 'sdms' ) . '</option>';

                                // Ajouter d'autres templates disponibles qui commencent par 'taxonomy-'
                                foreach ( $taxonomy_templates as $template ) {
                                    // Exclure les templates par défaut pour éviter les doublons
                                    if ( $template === 'taxonomy-template-default.php' ) {
                                        continue;
                                    }

                                    // Filtrer uniquement les templates de taxonomie
                                    if ( strpos( $template, 'taxonomy-' ) !== 0 ) {
                                        continue;
                                    }

                                    echo '<option value="' . esc_attr( $template ) . '" ' . selected( $selected_taxonomy_template, $template, false ) . '>' . esc_html( $template ) . '</option>';
                                }

                                ?>
                            </select>
                            <p class="description"><?php _e( 'Select a template for the taxonomy archives. You can create custom templates in your theme\'s sdms-templates folder.', 'sdms' ); ?></p>
                        </div>
                    </div>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <!-- JavaScript to handle dynamic functionality on the settings page -->
        <script>
            (function($){
                var availableLanguages = <?php echo json_encode( $available_languages ); ?>;

                // Fonction pour mettre à jour les options de sélection des langues
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

                // Gestion des clics sur les onglets
                $('.nav-tab').on('click', function(e){
                    e.preventDefault();
                    var target = $(this).attr('href');

                    // Retirer la classe active de tous les onglets et l'ajouter à celui cliqué
                    $('.nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');

                    // Masquer toutes les sections de contenu et afficher celle ciblée
                    $('.tab-content').hide();
                    $(target).show();
                });

                // Gestion du clic sur le bouton "Add Language"
                $('#sdms_add_language').on('click', function(){
                    var selectedCode = $('#sdms_language_selector').val();
                    if (!selectedCode) {
                        alert('<?php _e( "Veuillez sélectionner une langue à ajouter.", "sdms" ); ?>');
                        return;
                    }
                    var selectedLanguage = availableLanguages.find(function(lang) {
                        return lang.code === selectedCode;
                    });

                    if (selectedLanguage) {
                        // Générer le chemin de l'icône du drapeau
                        var flag_url = selectedLanguage.flag;

                        // Créer une nouvelle ligne dans le tableau des langues ajoutées
                        var newRow = '<tr>' +
                            '<td>' +
                                '<img src="' + flag_url + '" alt="' + selectedLanguage.lang + '" class="sdms-flag-image">' +
                            '</td>' +
                            '<td>' +
                                selectedLanguage.lang +
                            '</td>' +
                            '<td>' +
                                selectedCode +
                            '</td>' +
                            '<td>' +
                                '<button type="button" class="button sdms-remove-language" data-code="' + selectedCode + '">' + '<?php _e( 'Remove', 'sdms' ); ?>' + '</button>' +
                                '<input type="hidden" name="sdms_languages[' + selectedCode + '][lang]" value="' + selectedLanguage.lang + '">' +
                                '<input type="hidden" name="sdms_languages[' + selectedCode + '][flag]" value="' + flag_url + '">' +
                            '</td>' +
                        '</tr>';

                        // Ajouter la nouvelle ligne au tableau
                        $('#sdms_languages_table tbody').append(newRow);

                        // Mettre à jour les options du sélecteur
                        updateLanguageOptions();
                    }
                });

                // Gestion du clic sur le bouton "Remove Language"
                $(document).on('click', '.sdms-remove-language', function(){
                    var code = $(this).data('code');
                    $(this).closest('tr').remove();

                    // Ajouter de nouveau l'option au sélecteur
                    var language = availableLanguages.find(function(lang) {
                        return lang.code === code;
                    });
                    if (language) {
                        $('#sdms_language_selector').append('<option value="' + code + '">' + language.lang + '</option>');
                    }
                });

                // Gestion du clic sur le bouton "Change Icon"
                $(document).on('click', '.sdms-upload-icon-button', function(){
                    var fileType = $(this).data('file-type');
                    var button = $(this);

                    var frame = wp.media({
                        title: '<?php _e( "Choose Icon", "sdms" ); ?>',
                        button: { text: '<?php _e( "Use this icon", "sdms" ); ?>' },
                        library: { type: 'image' },
                        multiple: false
                    });

                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        // Mettre à jour l'URL de l'icône dans l'input caché
                        button.prev('input[type="hidden"]').val(attachment.url);
                        // Mettre à jour l'image affichée
                        button.closest('.sdms-file-type-icon').find('img.sdms-file-icon-image').attr('src', attachment.url);
                    });

                    frame.open();
                });

                // Initialiser les options de langue au chargement de la page
                $(document).ready(function() {
                    updateLanguageOptions();
                });
            })(jQuery);
        </script>
        <?php
    }

}
