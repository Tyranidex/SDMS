<?php
/**
 * Template Name: Archive Template One
 *
 * Description: An archive template displaying documents as cards.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-archive-template-one';
    return $classes;
} );

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <h1><?php post_type_archive_title(); ?></h1>

        <?php if ( have_posts() ) : ?>
            <div class="sdms-document-cards">
                <?php while ( have_posts() ) : the_post(); ?>
                    <div class="sdms-document-card">
                        <div class="sdms-card-header">
                            <?php
                            // Display the file type icon
                            $file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );
                            $icon_url = sdms_get_file_type_icon_url( $file_type );

                            // Display the icon if available
                            if ( $icon_url ) {
                                echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . '" class="sdms-file-type-icon">';
                            }
                            ?>

                            <!-- Post title with a link -->
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </div>

                        <div class="sdms-card-content">
                            <?php
                            // Display the excerpt
                            the_excerpt();
                            ?>
                        </div>

                        <div class="sdms-card-meta">
                            <!-- Categories -->
                            <p class="sdms-categories">
                                <?php
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
                            </p>

                            <!-- Last updated date -->
                            <p class="sdms-updated-date">
                                <?php echo esc_html__( 'Updated on', 'sdms' ) . ' ' . esc_html( get_the_modified_date() ); ?>
                            </p>
                        </div>

                        <div class="sdms-card-languages">
                            <?php
                            // Display the language flags with download links
                            sdms_display_language_links( get_the_ID() );
                            ?>

                            <!-- Optionally, add a default download link -->
                            <div class="sdms-default-download">
                                <?php
                                $download_url = sdms_get_download_url( get_the_ID() );
                                ?>
                                <a href="<?php echo esc_url( $download_url ); ?>" class="sdms-download-button">
                                    <?php esc_html_e( 'Download', 'sdms' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="sdms-card-actions">
                            <?php
                            // Button to send the document
                            ?>
                            <button class="sdms-send-document-button" data-post-id="<?php the_ID(); ?>">
                                <?php esc_html_e( 'Send Document', 'sdms' ); ?>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

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
?>
