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
        add_action( 'template_include', array( $this, 'load_custom_template' ) );

        // Handle file downloads
        add_action( 'wp', array( $this, 'handle_download' ) );

        // Enqueue front-end assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    /**
     * Enqueue front-end styles.
     */
    public function enqueue_frontend_assets() {
        if ( is_singular( 'sdms_document' ) ) {
            wp_enqueue_style( 'sdms-front-styles', sdms_PLUGIN_URL . 'assets/css/sdms-front-styles.css' );
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
        if ( $post->post_type !== 'sdms_document' ) {
            return $post_link;
        }

        if ( strpos( $post_link, '%sdms_category%' ) !== false ) {
            $terms = get_the_terms( $post->ID, 'sdms_category' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                // Replace the placeholder with the category slug
                $post_link = str_replace( '%sdms_category%', array_pop( $terms )->slug, $post_link );
            } else {
                // If no category assigned, use 'uncategorized'
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
        if ( get_query_var( 'sdms_document' ) ) {
            $selected_template = get_option( 'sdms_template', 'template-default.php' );
            $selected_template = sanitize_file_name( $selected_template );

            // Paths to template files
            $theme_template  = get_stylesheet_directory() . '/sdms-templates/' . $selected_template;
            $plugin_template = sdms_PLUGIN_DIR . 'templates/' . $selected_template;

            if ( file_exists( $theme_template ) ) {
                return $theme_template;
            } elseif ( file_exists( $plugin_template ) ) {
                return $plugin_template;
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
}
