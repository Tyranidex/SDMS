<?php
/**
 * Template Name: Single Template One
 *
 * Description: Un template alternatif pour les posts individuels de sdms_document.
 */
add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-single-template-one';
    return $classes;
} );
get_header();
?>

<div class="sdms-single-one">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <div class="sdms-document-header">
            <?php
            // Afficher la vignette du document s'il y en a une
            if ( has_post_thumbnail() ) {
                the_post_thumbnail( 'large' );
            }
            ?>
            <h1><?php the_title(); ?></h1>
        </div>

        <div class="sdms-document-content">
            <?php
            // Contenu complet du document
            the_content();
            ?>
        </div>

        <div class="sdms-document-files">
            <h2><?php _e( 'Fichiers Disponibles', 'sdms' ); ?></h2>
            <?php
            // Remplacement du shortcode [sdms_icon] par du PHP pour afficher l'icône du type de fichier
            $file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );
            $icon_url = '';

            if ( ! empty( $file_type ) ) {
                // Obtenir les icônes personnalisées à partir des options du plugin
                $file_type_icons = get_option( 'sdms_file_type_icons', array() );

                // Déterminer l'URL de l'icône
                if ( isset( $file_type_icons[ $file_type ] ) && ! empty( $file_type_icons[ $file_type ] ) ) {
                    $icon_url = $file_type_icons[ $file_type ];
                } else {
                    // Utiliser l'icône par défaut si aucune icône personnalisée n'est définie
                    $default_icon_path = sdms_PLUGIN_DIR . 'assets/images/icons/' . $file_type . '.png';
                    if ( file_exists( $default_icon_path ) ) {
                        $icon_url = sdms_PLUGIN_URL . 'assets/images/icons/' . $file_type . '.png';
                    }
                }
            }

            // Afficher l'icône si elle est disponible
            if ( ! empty( $icon_url ) ) {
                echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . '" class="sdms-file-type-icon">';
            }

            // Afficher les liens de téléchargement avec les drapeaux (comme précédemment)
            $languages = get_option( 'sdms_languages', array() );
            if ( is_array( $languages ) && ! empty( $languages ) ) {
                echo '<div class="sdms-language-links">';
                foreach ( $languages as $code => $language ) {
                    $file_id = get_post_meta( get_the_ID(), 'sdms_file_' . $code, true );
                    if ( $file_id ) {
                        $download_url = trailingslashit( get_permalink() ) . 'download/' . $code;
                        $flag_url = $language['flag'];
                        echo '<a href="' . esc_url( $download_url ) . '" target="_blank">';
                        echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" class="sdms-flag-icon">';
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
