<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class sdms_CPT
 *
 * Registers the custom post type 'sdms_document' and associated taxonomy 'sdms_category'.
 */
class sdms_CPT {

    public function __construct() {
        // Register custom post type and taxonomy on init
        add_action( 'init', array( $this, 'register_cpt' ) );
        add_action( 'init', array( $this, 'register_taxonomy' ) );
    }

    /**
     * Register the 'Document' custom post type.
     */
    public function register_cpt() {
        $labels = array(
            'name'               => __( 'Documents', 'sdms' ),
            'singular_name'      => __( 'Document', 'sdms' ),
            'add_new'            => __( 'Add New', 'sdms' ),
            'add_new_item'       => __( 'Add New Document', 'sdms' ),
            'edit_item'          => __( 'Edit Document', 'sdms' ),
            'new_item'           => __( 'New Document', 'sdms' ),
            'all_items'          => __( 'All Documents', 'sdms' ),
            'view_item'          => __( 'View Document', 'sdms' ),
            'search_items'       => __( 'Search Documents', 'sdms' ),
            'not_found'          => __( 'No documents found', 'sdms' ),
            'not_found_in_trash' => __( 'No documents found in Trash', 'sdms' ),
            'menu_name'          => __( 'Documents', 'sdms' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-media-document',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'has_archive'        => 'docs',
            'rewrite'            => array(
                'slug'       => 'docs/%sdms_category%',
                'with_front' => false,
            ),
        );

        // Register the custom post type
        register_post_type( 'sdms_document', $args );
    }

    /**
     * Register custom taxonomy for documents.
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => __( 'Document Categories', 'sdms' ),
            'singular_name'     => __( 'Document Category', 'sdms' ),
            'search_items'      => __( 'Search Categories', 'sdms' ),
            'all_items'         => __( 'All Categories', 'sdms' ),
            'parent_item'       => __( 'Parent Category', 'sdms' ),
            'parent_item_colon' => __( 'Parent Category:', 'sdms' ),
            'edit_item'         => __( 'Edit Category', 'sdms' ),
            'update_item'       => __( 'Update Category', 'sdms' ),
            'add_new_item'      => __( 'Add New Category', 'sdms' ),
            'new_item_name'     => __( 'New Category Name', 'sdms' ),
            'menu_name'         => __( 'Categories', 'sdms' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array(
                'slug'       => 'document-category',
                'with_front' => false,
            ),
        );

        // Register the custom taxonomy
        register_taxonomy( 'sdms_category', array( 'sdms_document' ), $args );
    }
}
