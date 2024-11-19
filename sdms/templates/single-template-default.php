<?php
/**
 * Template Name: Single Template Default
 *
 * Description: The default template for individual sdms_document posts.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-single-template-default';
    return $classes;
} );

get_header();
?>

<div class="sdms-document-downloads">
    <h2><?php esc_html_e( 'Downloads', 'sdms' ); ?></h2>

    <?php
    // Display language links
    sdms_display_language_links( get_the_ID() );
    ?>

    <!-- Display a default download button -->
    <div class="sdms-default-download">
        <?php
        // Generate default download URL without specifying a language
        $download_url = sdms_get_download_url( get_the_ID() );
        ?>
        <a href="<?php echo esc_url( $download_url ); ?>" class="sdms-download-button">
            <?php esc_html_e( 'Download Default Version', 'sdms' ); ?>
        </a>
    </div>
</div>


<?php
get_footer();
