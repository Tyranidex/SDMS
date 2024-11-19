<?php
/**
 * Template Name: Single Template Two
 *
 * Description: Another template for individual sdms_document posts.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-single-template-two';
    return $classes;
} );

get_header();
?>

<div class="sdms-single-two">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h1><?php the_title(); ?></h1>

        <div class="sdms-document-excerpt">
            <?php
            // Display an excerpt of the document
            the_excerpt();
            ?>
        </div>

        <div class="sdms-document-download-button">
            <?php
            // Display primary download buttons for available languages
            sdms_display_download_buttons( get_the_ID() );
            ?>
        </div>

    <?php endwhile; endif; ?>
</div>

<?php
get_footer();
