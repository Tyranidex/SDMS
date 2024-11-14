<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class sdms_Frontend
 *
 * Handles front-end functionalities, including custom permalinks, rewrite rules,
 * template loading, file downloads, and enqueueing front-end assets.
 */
class sdms_Frontend {

    public function __construct() {
        // Modify the permalink structure
        add_filter( 'post_type_link', array( $this, 'custom_post_link' ), 10, 2 );

        // Add custom rewrite rules
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );

        // Load custom template for the 'sdms_document' post type
        add_filter( 'template_include', array( $this, 'load_custom_template' ) );

        // Handle file downloads
        add_action( 'wp', array( $this, 'handle_download' ) );

        // Enqueue front-end assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Adjust main query
        add_action( 'pre_get_posts', array( $this, 'adjust_sdms_archive_query' ) );

        // Ajax Action for sharing documents
        add_action( 'wp_ajax_sdms_send_document', array( $this, 'handle_send_document_ajax' ) );
        add_action( 'wp_ajax_nopriv_sdms_send_document', array( $this, 'handle_send_document_ajax' ) );

    }

    /**
     * Adjust the main query for the sdms_document archive page.
     *
     * @param WP_Query $query The main query object.
     */
    public function adjust_sdms_archive_query( $query ) {
        if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'sdms_document' ) ) {
            // Set the number of posts per page
            $query->set( 'posts_per_page', 20 );
        }
    }

    /**
     * Enqueue front-end styles.
     */
    public function enqueue_frontend_assets() {
        if ( is_singular( 'sdms_document' ) || is_post_type_archive( 'sdms_document' ) ) {
            wp_enqueue_style( 'sdms-front-styles', sdms_PLUGIN_URL . 'assets/css/sdms-front-styles.css' );

            // Enregistrer le script pour la modale et l'AJAX
            wp_enqueue_script( 'sdms-front-script', sdms_PLUGIN_URL . 'assets/js/sdms-front-script.js', array( 'jquery' ), '1.0.0', true );

            // Localiser le script pour passer l'URL AJAX et le nonce
            wp_localize_script( 'sdms-front-script', 'sdmsAjax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'sdms_send_document_nonce' ),
            ) );
        }
    }

    /**
     * Modify the permalink structure to include the category slug.
     *
     * @param string  $post_link The original permalink.
     * @param WP_Post $post      The post object.
     * @return string Modified permalink.
     */
    public function custom_post_link( $post_link, $post ) {
        if ( $post->post_type == 'sdms_document' ) {
            $terms = wp_get_post_terms( $post->ID, 'sdms_category' );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $post_link = str_replace( '%sdms_category%', $terms[0]->slug, $post_link );
            } else {
                $post_link = str_replace( '%sdms_category%', 'uncategorized', $post_link );
            }
        }
        return $post_link;
    }

    /**
     * Add custom rewrite rules for pretty permalinks.
     */
    public function add_rewrite_rules() {
        // Add rewrite tags
        add_rewrite_tag( '%sdms_document%', '([^/]+)', 'sdms_document=' );
        add_rewrite_tag( '%sdms_category%', '([^/]+)', 'sdms_category=' );
        add_rewrite_tag( '%language%', '([^/]+)', 'language=' );
        add_rewrite_tag( '%sdms_download%', '([0-1]{1})', 'sdms_download=' );

        // Rewrite rules for documents with category and download
        add_rewrite_rule(
            '^docs/([^/]+)/([^/]+)/download/([a-z]{2})/?$',
            'index.php?sdms_category=$matches[1]&sdms_document=$matches[2]&sdms_download=1&language=$matches[3]',
            'top'
        );

        // Rewrite rules for documents with category without download
        add_rewrite_rule(
            '^docs/([^/]+)/([^/]+)/?$',
            'index.php?sdms_category=$matches[1]&sdms_document=$matches[2]',
            'top'
        );

        // Rewrite rules for documents without category with download
        add_rewrite_rule(
            '^docs/([^/]+)/download/([a-z]{2})/?$',
            'index.php?sdms_document=$matches[1]&sdms_download=1&language=$matches[2]',
            'top'
        );

        // Rewrite rules for documents without category without download
        add_rewrite_rule(
            '^docs/([^/]+)/?$',
            'index.php?sdms_document=$matches[1]',
            'top'
        );

        // Add query variables
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
    }

    /**
     * Add custom query variables.
     *
     * @param array $vars Existing query variables.
     * @return array Modified query variables.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'sdms_download';
        $vars[] = 'language';
        $vars[] = 'sdms_document';
        $vars[] = 'sdms_category';
        return $vars;
    }

    /**
     * Load a custom template for the 'sdms_document' post type.
     *
     * @param string $template The path to the template.
     * @return string Modified template path.
     */
    public function load_custom_template( $template ) {
        // Récupérer les templates sélectionnés dans les options
        $selected_single_template = get_option( 'sdms_template', 'single-template-default.php' );
        $selected_archive_template = get_option( 'sdms_archive_template', 'archive-template-default.php' );

        // Sanitize filenames
        $selected_single_template = sanitize_file_name( $selected_single_template );
        $selected_archive_template = sanitize_file_name( $selected_archive_template );

        // Chemins vers les templates
        $theme_single_template  = get_stylesheet_directory() . '/sdms-templates/' . $selected_single_template;
        $plugin_single_template = sdms_PLUGIN_DIR . 'templates/' . $selected_single_template;

        $theme_archive_template  = get_stylesheet_directory() . '/sdms-templates/' . $selected_archive_template;
        $plugin_archive_template = sdms_PLUGIN_DIR . 'templates/' . $selected_archive_template;

        // Vérifier si nous sommes sur un post individuel de sdms_document
        if ( is_singular( 'sdms_document' ) ) {
            if ( file_exists( $theme_single_template ) ) {
                return $theme_single_template;
            } elseif ( file_exists( $plugin_single_template ) ) {
                return $plugin_single_template;
            }
        }
        // Vérifier si nous sommes sur la page d'archive de sdms_document
        elseif ( is_post_type_archive( 'sdms_document' ) ) {
            if ( file_exists( $theme_archive_template ) ) {
                return $theme_archive_template;
            } elseif ( file_exists( $plugin_archive_template ) ) {
                return $plugin_archive_template;
            }
        }
        return $template;
    }

    /**
     * Handle file downloads.
     */
    public function handle_download() {
        if ( get_query_var( 'sdms_download' ) ) {
            $document_slug = get_query_var( 'sdms_document' );
            $category_slug = get_query_var( 'sdms_category' );
            $language      = get_query_var( 'language', 'en' );

            // Build the path
            $path = $category_slug ? $category_slug . '/' . $document_slug : $document_slug;

            // Find the post based on the path
            $post = $this->get_post_by_path( $path );
            if ( $post ) {
                // Get the file ID for the specified language
                $file_id = get_post_meta( $post->ID, 'sdms_file_' . $language, true );
                if ( $file_id ) {
                    $file_path = get_attached_file( $file_id );
                    if ( $file_path && file_exists( $file_path ) ) {
                        // Serve the file content directly
                        $file_type = wp_check_filetype( $file_path );
                        $mime_type = $file_type['type'];
                        $file_name = basename( $file_path );

                        // Set headers for file download
                        header( 'Content-Description: File Transfer' );
                        header( 'Content-Type: ' . $mime_type );
                        header( 'Content-Disposition: inline; filename="' . $file_name . '"' );
                        header( 'Content-Transfer-Encoding: binary' );
                        header( 'Expires: 0' );
                        header( 'Cache-Control: must-revalidate' );
                        header( 'Pragma: public' );
                        header( 'Content-Length: ' . filesize( $file_path ) );

                        // Clean output buffer
                        ob_clean();
                        flush();

                        // Read the file
                        readfile( $file_path );
                        exit;
                    }
                }
            }
            // If no file found, display 404
            wp_die( __( 'File not found.', 'sdms' ), 404 );
        }
    }

    /**
     * Helper function to get a post by its path.
     *
     * @param string $path The path to the post.
     * @return WP_Post|null The post object or null if not found.
     */
    private function get_post_by_path( $path ) {
        $path_parts = explode( '/', trim( $path, '/' ) );

        if ( count( $path_parts ) == 2 ) {
            // If path includes category and post name
            $category_slug = $path_parts[0];
            $post_name     = $path_parts[1];
        } else {
            // If path includes only post name
            $category_slug = '';
            $post_name     = $path_parts[0];
        }

        // Prepare query arguments
        $args = array(
            'name'        => $post_name,
            'post_type'   => 'sdms_document',
            'post_status' => 'publish',
            'numberposts' => 1,
        );

        // If category is present, include it in the query
        if ( $category_slug ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'sdms_category',
                    'field'    => 'slug',
                    'terms'    => $category_slug,
                ),
            );
        }

        $posts = get_posts( $args );
        if ( $posts ) {
            return $posts[0];
        }
        return null;
    }

    public function handle_send_document_ajax() {
        // Vérifier le nonce
        check_ajax_referer( 'sdms_send_document_nonce', 'nonce' );

        // Récupérer les données POST
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $recipient_email = isset( $_POST['recipient_email'] ) ? sanitize_email( $_POST['recipient_email'] ) : '';

        // Valider l'email
        if ( ! is_email( $recipient_email ) ) {
            wp_send_json_error( array( 'message' => __( 'Adresse email invalide.', 'sdms' ) ) );
        }

        // Vérifier si le post existe et est du bon type
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type != 'sdms_document' ) {
            wp_send_json_error( array( 'message' => __( 'Document invalide.', 'sdms' ) ) );
        }

        // Préparer le contenu de l'email
        $subject = sprintf( __( 'Consultez ce document : %s', 'sdms' ), get_the_title( $post_id ) );
        $permalink = get_permalink( $post_id );
        $message = sprintf( __( 'Bonjour,%sVous pourriez être intéressé par ce document : %s%sVous pouvez le consulter ici : %s', 'sdms' ), "\n\n", get_the_title( $post_id ), "\n", $permalink );

        // Envoyer l'email
        $sent = wp_mail( $recipient_email, $subject, $message );

        if ( $sent ) {
            wp_send_json_success( array( 'message' => __( 'Email envoyé avec succès.', 'sdms' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Échec de l\'envoi de l\'email. Veuillez réessayer plus tard.', 'sdms' ) ) );
        }
    }
}
