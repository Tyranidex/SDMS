<?php
/**
 * Template Name: Default Taxonomy Template
 *
 * Description: The default template for sdms_category taxonomy archives.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-taxonomy-template-default';
    return $classes;
} );

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <h1><?php single_term_title(); ?></h1>

        <?php if ( have_posts() ) : ?>
            <table class="sdms-document-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Document', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Description', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Categories', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Last Updated', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Languages', 'sdms' ); ?></th>
                        <th><?php esc_html_e( 'Share', 'sdms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <tr>
                            <td>
                                <?php
                                // Display the file type icon
                                $file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );
                                $icon_url = sdms_get_file_type_icon_url( $file_type );

                                // Display the icon if available
                                if ( $icon_url ) {
                                    echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . '" class="sdms-file-type-icon">';
                                }

                                // Display the post title with a link
                                ?>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </td>
                            <td>
                                <?php
                                // Display the excerpt
                                the_excerpt();
                                ?>
                            </td>
                            <td>
                                <?php
                                // Display the categories
                                $terms = get_the_terms( get_the_ID(), 'sdms_category' );
                                if ( $terms && ! is_wp_error( $terms ) ) {
                                    $categories = array();
                                    foreach ( $terms as $term ) {
                                        $categories[] = '<a href="' . get_term_link( $term ) . '">' . esc_html( $term->name ) . '</a>';
                                    }
                                    echo implode( ', ', $categories );
                                } else {
                                    esc_html_e( 'Uncategorized', 'sdms' );
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                // Display the last modified date
                                echo esc_html( get_the_modified_date() );
                                ?>
                            </td>
                            <td>
                                <?php
                                // Display the flags with links to view the documents
                                sdms_display_language_links( get_the_ID() );
                                ?>
                            </td>
                            <td>
                                <?php
                                // Button to send the document
                                ?>
                                <button class="sdms-send-document-button" data-post-id="<?php the_ID(); ?>">
                                    <?php esc_html_e( 'Send Document', 'sdms' ); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="sdms-pagination">
                <?php
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => __( 'Previous', 'sdms' ),
                    'next_text' => __( 'Next', 'sdms' ),
                ) );
                ?>
            </div>
        <?php else : ?>
            <p><?php esc_html_e( 'No documents found.', 'sdms' ); ?></p>
        <?php endif; ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
