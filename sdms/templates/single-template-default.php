<?php
/**
 * Template Name: Single Template Default
 *
 * Description: Le template par défaut pour les posts individuels de sdms_document.
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-single-template-default';
    return $classes;
} );
get_header();
?>

<div class="sdms-single-default">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h1><?php the_title(); ?></h1>

        <div class="sdms-document-meta">
            <?php
            // Afficher la date et l'auteur
            echo '<p>' . get_the_date() . ' - ' . get_the_author() . '</p>';
            ?>
        </div>

        <div class="sdms-document-content">
            <?php
            // Contenu complet du document
            the_content();
            ?>
        </div>

        <div class="sdms-document-downloads">
            <h2><?php _e( 'Téléchargements', 'sdms' ); ?></h2>
            <?php
            // Afficher les liens de téléchargement avec les drapeaux
            // Remplacement du shortcode [sdms_flags] par du code PHP

            // Récupérer les langues disponibles
            $languages = get_option( 'sdms_languages', array() );
            if ( is_array( $languages ) && ! empty( $languages ) ) {
                echo '<div class="sdms-language-links">';
                foreach ( $languages as $code => $language ) {
                    // Vérifier si un fichier est associé à cette langue
                    $file_id = get_post_meta( get_the_ID(), 'sdms_file_' . $code, true );
                    if ( $file_id ) {
                        // Générer l'URL de téléchargement
                        $download_url = trailingslashit( get_permalink() ) . 'download/' . $code;

                        // Déterminer l'URL du drapeau
                        $flag_url = ! empty( $language['custom_flag'] ) ? $language['custom_flag'] : $language['flag'];

                        // Afficher l'image du drapeau avec le lien de téléchargement
                        echo '<a href="' . esc_url( $download_url ) . '" target="_blank">';
                        echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['country'] ) . '" class="sdms-flag-icon">';
                        echo '</a>';
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
