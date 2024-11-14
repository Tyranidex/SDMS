<?php
/**
 * Template Name: Single Template Two
 *
 * Description: Un autre template pour les posts individuels de sdms_document.
 */
// Ajoutez une classe au body
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
            // Afficher un extrait du document
            the_excerpt();
            ?>
        </div>

        <div class="sdms-document-download-button">
            <?php
            // Afficher un bouton de téléchargement principal
            // On peut choisir de télécharger le fichier par défaut ou afficher une liste si plusieurs langues sont disponibles
            $languages = get_option( 'sdms_languages', array() );
            if ( is_array( $languages ) && ! empty( $languages ) ) {
                echo '<div class="sdms-download-buttons">';
                foreach ( $languages as $code => $language ) {
                    $file_id = get_post_meta( get_the_ID(), 'sdms_file_' . $code, true );
                    if ( $file_id ) {
                        $download_url = trailingslashit( get_permalink() ) . 'download/' . $code;
                        $language_name = $language['lang'];
                        echo '<a href="' . esc_url( $download_url ) . '" class="sdms-download-button">' . sprintf( __( 'Télécharger (%s)', 'sdms' ), esc_html( $language_name ) ) . '</a>';
                    }
                }
                echo '</div>';
            } else {
                echo '<p>' . __( 'Aucun fichier disponible pour le téléchargement.', 'sdms' ) . '</p>';
            }
            ?>
        </div>

    <?php endwhile; endif; ?>
</div>

<?php
get_footer();
?>
