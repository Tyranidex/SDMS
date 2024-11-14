<?php
/**
 * Template Name: Archive Template One
 *
 * Description: Un template alternatif pour les pages d'archive de sdms_document.
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-archive-template-one';
    return $classes;
} );
get_header();
?>

<div class="sdms-archive-one">
    <h1><?php _e( 'Liste des Documents', 'sdms' ); ?></h1>

    <?php if ( have_posts() ) : ?>
        <ul class="sdms-document-grid">
            <?php while ( have_posts() ) : the_post(); ?>
                <li class="sdms-document-grid-item">
                    <?php
                    // Afficher la vignette du document s'il y en a une
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'medium' );
                    }
                    ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                </li>
            <?php endwhile; ?>
        </ul>

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
