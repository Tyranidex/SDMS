<?php
/**
 * Template Name: Archive Template Default
 *
 * Description: Le template par défaut pour les pages d'archive de sdms_document.
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-archive-template-default';
    return $classes;
} );
get_header();
?>

<div class="sdms-archive-default">
    <h1><?php post_type_archive_title(); ?></h1>

    <?php if ( have_posts() ) : ?>
        <div class="sdms-document-list">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="sdms-document-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php
                    // Afficher un extrait du contenu
                    the_excerpt();
                    ?>
                </div>
            <?php endwhile; ?>
        </div>

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
