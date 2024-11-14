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
            // Récupérer le type d'icône de drapeau sélectionné
            $selected_flag_icon_type = get_option( 'sdms_flag_icon_type', 'squared' );

            // Si 'custom' est sélectionné mais que le dossier n'existe pas, revenir à 'squared'
            if ( $selected_flag_icon_type === 'custom' ) {
                $custom_flags_dir = get_stylesheet_directory() . '/sdms-flags/';
                if ( ! ( file_exists( $custom_flags_dir ) && is_dir( $custom_flags_dir ) ) ) {
                    $selected_flag_icon_type = 'squared';
                }
            }

            // Récupérer les langues configurées
            $languages = get_option( 'sdms_languages', array() );

            if ( is_array( $languages ) && ! empty( $languages ) ) {
                echo '<div class="sdms-language-links">';
                foreach ( $languages as $code => $language ) {
                    // Vérifier si un fichier est associé à cette langue
                    $file_id = get_post_meta( get_the_ID(), 'sdms_file_' . $code, true );
                    if ( $file_id ) {
                        // Générer l'URL de téléchargement
                        $download_url = trailingslashit( get_permalink() ) . 'download/' . $code;

                        // Déterminer le chemin de l'icône du drapeau
                        if ( $selected_flag_icon_type === 'custom' ) {
                            // Chemin vers le drapeau personnalisé dans le thème
                            $flag_file = get_stylesheet_directory() . '/sdms-flags/' . $code . '.png';
                            $flag_url  = get_stylesheet_directory_uri() . '/sdms-flags/' . $code . '.png';
                            if ( ! file_exists( $flag_file ) ) {
                                // Si le drapeau personnalisé n'existe pas, utiliser un drapeau par défaut
                                $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                            }
                        } else {
                            // Chemin vers le drapeau du plugin
                            $flag_file = sdms_PLUGIN_DIR . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                            $flag_url  = sdms_PLUGIN_URL . 'assets/images/flags/' . $selected_flag_icon_type . '/' . $code . '.png';
                            if ( ! file_exists( $flag_file ) ) {
                                // Si le drapeau n'existe pas, utiliser un drapeau par défaut
                                $flag_url = sdms_PLUGIN_URL . 'assets/images/default-flag.png';
                            }
                        }

                        // Afficher l'image du drapeau avec le lien de téléchargement
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