<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SDMS_Frontend
 *
 * Handles front-end functionalities, including custom permalinks, rewrite rules,
 * template loading, file downloads, and enqueueing front-end assets.
 */
class SDMS_Frontend {

    private $sdms_is_document_search = false;

    public function __construct() {
        // Modify the permalink structure
        add_filter( 'post_type_link', array( $this, 'custom_post_link' ), 10, 2 );

        // Add custom rewrite rules
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );

        // Load custom template for the 'sdms_document' post type
        add_filter( 'template_include', array( $this, 'load_custom_template' ), 99 );

        // Handle file downloads
        add_action( 'wp', array( $this, 'handle_download' ) );

        // Enqueue front-end assets
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

        // Adjust main query
        add_action( 'pre_get_posts', array( $this, 'adjust_sdms_archive_query' ) );

        // AJAX Action for sharing documents
        add_action( 'wp_ajax_sdms_send_document', array( $this, 'handle_send_document_ajax' ) );
        add_action( 'wp_ajax_nopriv_sdms_send_document', array( $this, 'handle_send_document_ajax' ) );

        // Include the modal in the footer
        add_action( 'wp_footer', array( $this, 'include_send_document_modal' ) );

        // Check access when viewing single documents
        add_action( 'template_redirect', array( $this, 'check_single_document_access' ) );

        add_action( 'pre_get_posts', array( $this, 'detect_sdms_document_search' ) );
    }

    /**
     * Adjust the main query for the sdms_document archive page.
     *
     * @param WP_Query $query The main query object.
     */
    public function adjust_sdms_archive_query( $query ) {
        if ( ! is_admin() && $query->is_main_query() && ( is_post_type_archive( 'sdms_document' ) || is_tax( 'sdms_category' ) ) ) {
            // Set the number of posts per page
            $query->set( 'posts_per_page', 40 );
        }
    }

    /**
     * Enqueue front-end styles and scripts.
     */
    public function enqueue_frontend_assets() {
        if ( is_singular( 'sdms_document' ) || is_post_type_archive( 'sdms_document' ) || is_tax( 'sdms_category' ) ) {
            wp_enqueue_style( 'sdms-front-styles', SDMS_PLUGIN_URL . 'assets/css/sdms-front-styles.css', array(), '1.0.0' );

            // Enqueue the script for the modal
            wp_enqueue_script( 'sdms-front-script', SDMS_PLUGIN_URL . 'assets/js/sdms-front-script.js', array( 'jquery' ), '1.0.0', true );

            // Localize script to pass AJAX URL and nonce for the modal
            wp_localize_script( 'sdms-front-script', 'sdmsAjaxModal', array(
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

        // Rewrite rules for documents with category and download with language
        add_rewrite_rule(
            '^docs/([^/]+)/([^/]+)/download/([a-z]{2})/?$',
            'index.php?sdms_category=$matches[1]&sdms_document=$matches[2]&sdms_download=1&language=$matches[3]',
            'top'
        );

        // Rewrite rules for documents with category and download without language
        add_rewrite_rule(
            '^docs/([^/]+)/([^/]+)/download/?$',
            'index.php?sdms_category=$matches[1]&sdms_document=$matches[2]&sdms_download=1',
            'top'
        );

        // Rewrite rules for documents without category with download and language
        add_rewrite_rule(
            '^docs/([^/]+)/download/([a-z]{2})/?$',
            'index.php?sdms_document=$matches[1]&sdms_download=1&language=$matches[2]',
            'top'
        );

        // Rewrite rules for documents without category with download without language
        add_rewrite_rule(
            '^docs/([^/]+)/download/?$',
            'index.php?sdms_document=$matches[1]&sdms_download=1',
            'top'
        );

        // Rewrite rules for documents with category without download
        add_rewrite_rule(
            '^docs/([^/]+)/([^/]+)/?$',
            'index.php?sdms_category=$matches[1]&sdms_document=$matches[2]',
            'top'
        );

        // Rewrite rules for documents without category without download
        add_rewrite_rule(
            '^docs/([^/]+)/?$',
            'index.php?sdms_document=$matches[1]',
            'top'
        );

        // Rewrite rules for sdms_category taxonomy archives
        add_rewrite_rule(
            '^document-category/([^/]+)/?$',
            'index.php?sdms_category=$matches[1]',
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
        error_log( 'load_custom_template called with template: ' . $template );
        // Récupère les templates sélectionnés depuis les options
        $selected_single_template   = get_option( 'sdms_template', 'single-template-default.php' );
        $selected_archive_template  = get_option( 'sdms_archive_template', 'archive-template-default.php' );
        $selected_taxonomy_template = get_option( 'sdms_taxonomy_template', 'taxonomy-template-default.php' );
        $selected_search_template   = 'search-template-default.php'; // Vous pouvez le rendre configurable si nécessaire

        // Sanitize filenames
        $selected_single_template   = sanitize_file_name( $selected_single_template );
        $selected_archive_template  = sanitize_file_name( $selected_archive_template );
        $selected_taxonomy_template = sanitize_file_name( $selected_taxonomy_template );
        $selected_search_template   = sanitize_file_name( $selected_search_template );

        // Paths to templates
        $theme_templates_dir = get_stylesheet_directory() . '/sdms-templates/';

        // Determine the correct template path
        if ( is_singular( 'sdms_document' ) ) {
            $template_file = $selected_single_template;
        } elseif ( is_post_type_archive( 'sdms_document' ) ) {
            $template_file = $selected_archive_template;
        } elseif ( is_tax( 'sdms_category' ) ) {
            $template_file = $selected_taxonomy_template;
        } elseif ( is_search() && $this->sdms_is_document_search ) {
            $template_file = $selected_search_template;
        } else {
            return $template;
        }

        // Check theme directory first
        $theme_template = $theme_templates_dir . $template_file;
        if ( file_exists( $theme_template ) ) {
            return $theme_template;
        }

        // Fallback to plugin directory
        $plugin_template = SDMS_PLUGIN_DIR . 'templates/' . $template_file;
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
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
            $language      = get_query_var( 'language', '' ); // Default to empty string

            // Build the path
            $path = $category_slug ? $category_slug . '/' . $document_slug : $document_slug;

            // Find the post based on the path
            $post = $this->get_post_by_path( $path );
            if ( $post ) {
                // Access control check
                if ( ! sdms_user_can_view( $post->ID ) ) {
                    if ( ! is_user_logged_in() ) {
                        // Redirect non-logged-in users to login page
                        auth_redirect();
                    } else {
                        // Display 'not authorized' message
                        wp_die( __( 'You do not have permission to download this file.', 'sdms' ), 403 );
                    }
                }

                if ( empty( $language ) ) {
                    // If language is not specified, get the default or fallback language
                    $language = sdms_get_default_language( $post->ID );
                }

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

    /**
     * Include the modal template in the footer.
     */
    public function include_send_document_modal() {
        if ( is_singular( 'sdms_document' ) || is_post_type_archive( 'sdms_document' ) || is_tax( 'sdms_category' ) ) {
            // Path to the modal template
            $template = SDMS_PLUGIN_DIR . 'includes/send-document-modal.php';

            if ( file_exists( $template ) ) {
                include( $template );
            }
        }
    }

    /**
     * Handle AJAX request to send the document via email.
     */
    public function handle_send_document_ajax() {
        // Verify the nonce
        check_ajax_referer( 'sdms_send_document_nonce', 'nonce' );

        // Get POST data
        $post_id         = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $sender_name     = isset( $_POST['sender_name'] ) ? sanitize_text_field( $_POST['sender_name'] ) : '';
        $recipient_email = isset( $_POST['recipient_email'] ) ? sanitize_email( $_POST['recipient_email'] ) : '';

        // Validate sender name
        if ( empty( $sender_name ) ) {
            wp_send_json_error( array( 'message' => __( 'Please enter your name.', 'sdms' ) ) );
        }

        // Validate email
        if ( ! is_email( $recipient_email ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid email address.', 'sdms' ) ) );
        }

        // Check if the post exists and is of the correct type
        $post = get_post( $post_id );
        if ( ! $post || $post->post_type != 'sdms_document' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid document.', 'sdms' ) ) );
        }

        // Prepare email content
        $subject   = sprintf( __( 'Check out this document: %s', 'sdms' ), get_the_title( $post_id ) );
        $permalink = get_permalink( $post_id );

        // Build the message in HTML
        $message = '
        <html>
        <head>
            <title>' . esc_html( $subject ) . '</title>
        </head>
        <body>
            <p>' . __( 'Hello,', 'sdms' ) . '</p>
            <p>' . esc_html( $sender_name ) . ' ' . __( 'has shared a document with you:', 'sdms' ) . ' <strong>' . esc_html( get_the_title( $post_id ) ) . '</strong></p>
            <p>
                <a href="' . esc_url( $permalink ) . '" style="
                    display: inline-block;
                    padding: 10px 20px;
                    font-size: 16px;
                    color: #ffffff;
                    background-color: #0073aa;
                    text-decoration: none;
                    border-radius: 5px;
                ">' . __( 'View Document', 'sdms' ) . '</a>
            </p>
            <p>' . __( 'Have a great day!', 'sdms' ) . '</p>
        </body>
        </html>
        ';

        // Set headers for HTML email
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // Send the email
        $sent = wp_mail( $recipient_email, $subject, $message, $headers );

        if ( $sent ) {
            wp_send_json_success( array( 'message' => __( 'Email sent successfully.', 'sdms' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to send email. Please try again later.', 'sdms' ) ) );
        }
    }

    /**
     * Check access when viewing single documents.
     */
    public function check_single_document_access() {
        if ( is_singular( 'sdms_document' ) ) {
            $post_id = get_the_ID();

            // Access control check
            if ( ! sdms_user_can_view( $post_id ) ) {
                if ( ! is_user_logged_in() ) {
                    // Redirect non-logged-in users to login page
                    auth_redirect();
                } else {
                    // Display 'not authorized' message
                    wp_die( __( 'You do not have permission to view this document.', 'sdms' ), 403 );
                }
            }
        }
    }

    public function detect_sdms_document_search( $query ) {
        if ( $query->is_search() && ! is_admin() && $query->is_main_query() ) {
            $post_type = $query->get( 'post_type' );
            if ( $post_type == 'sdms_document' || ( is_array( $post_type ) && in_array( 'sdms_document', $post_type ) ) ) {
                $this->sdms_is_document_search = true;
            }
        }
    }
}

