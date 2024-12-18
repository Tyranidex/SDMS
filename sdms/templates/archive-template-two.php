<?php
/**
 * Template Name: Archive Template Two
 *
 * Description: Another archive template for sdms_document pages.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-archive-template-two';
    return $classes;
} );

get_header();
?>

<div class="sdms-archive-two">
    <h1><?php esc_html_e( 'Available Documents', 'sdms' ); ?></h1>

    <?php if ( have_posts() ) : ?>
        <table class="sdms-document-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Title', 'sdms' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'sdms' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ( have_posts() ) : the_post(); ?>
                    <tr>
                        <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                        <td><?php echo esc_html( get_the_date() ); ?></td>
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
</div>

<?php
get_footer();
?>
