<?php
/**
 * Template Name: Single Template One
 *
 * Description: An alternative template for individual sdms_document posts.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-single-template-one';
    return $classes;
} );

get_header();
?>

<div class="sdms-document-files">
    <h2><?php esc_html_e( 'Available Files', 'sdms' ); ?></h2>
    <?php
    // Display the file type icon
    $file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );
    $icon_url = sdms_get_file_type_icon_url( $file_type );

    // Display the icon if available
    if ( $icon_url ) {
        echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . '" class="sdms-file-type-icon">';
    }

    // Display language links
    sdms_display_language_links( get_the_ID() );
    ?>

    <!-- Display a default download button -->
    <div class="sdms-default-download">
        <?php
        $download_url = sdms_get_download_url( get_the_ID() );
        ?>
        <a href="<?php echo esc_url( $download_url ); ?>" class="sdms-download-button">
            <?php esc_html_e( 'Download Default Version', 'sdms' ); ?>
        </a>
    </div>
</div>

<?php
get_footer();
