<?php
/**
 * Template Name: Archive Template Two
 *
 * Description: Un autre template pour les pages d'archive de sdms_document.
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-archive-template-two';
    return $classes;
} );
get_header();
?>

<div class="sdms-archive-two">
    <h1><?php _e( 'Documents Disponibles', 'sdms' ); ?></h1>

    <?php if ( have_posts() ) : ?>
        <table class="sdms-document-table">
            <thead>
                <tr>
                    <th><?php _e( 'Titre', 'sdms' ); ?></th>
                    <th><?php _e( 'Date', 'sdms' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ( have_posts() ) : the_post(); ?>
                    <tr>
                        <td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
                        <td><?php echo get_the_date(); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="sdms-pagination">
            <?php
            the_posts_pagination( array(
                'mid_size' => 2,
                'prev_text' => __( 'Précédent', 'sdms' ),
                'next_text' => __( 'Suivant', 'sdms' ),
            ) );
            ?>
        </div>
    <?php else : ?>
        <p><?php _e( 'Aucun document trouvé.', 'sdms' ); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
?>
