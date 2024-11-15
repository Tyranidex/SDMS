<?php
/**
 * Template Name: Default Taxonomy Template
 *
 * Description: The default template for sdms_category taxonomy archives.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-taxonomy-template-default';
    return $classes;
} );

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <h1><?php post_type_archive_title(); ?></h1>

        <?php if ( have_posts() ) : ?>
            <table class="sdms-document-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Document', 'sdms' ); ?></th>
                        <th><?php _e( 'Description', 'sdms' ); ?></th>
                        <th><?php _e( 'Categories', 'sdms' ); ?></th>
                        <th><?php _e( 'Last Updated', 'sdms' ); ?></th>
                        <th><?php _e( 'Languages', 'sdms' ); ?></th>
                        <th><?php _e( 'Share', 'sdms' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <tr>
                            <td>
                                <?php
                                // Afficher l'icône du type de fichier
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

                                // Afficher le titre du post avec le lien
                                ?>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </td>
                            <td>
                                <?php
                                // Afficher le contenu ou l'extrait
                                the_excerpt();
                                ?>
                            </td>
                            <td>
                                <?php
                                // Afficher les catégories
                                $terms = get_the_terms( get_the_ID(), 'sdms_category' );
                                if ( $terms && ! is_wp_error( $terms ) ) {
                                    $categories = array();
                                    foreach ( $terms as $term ) {
                                        $categories[] = '<a href="' . get_term_link( $term ) . '">' . esc_html( $term->name ) . '</a>';
                                    }
                                    echo implode( ', ', $categories );
                                } else {
                                    _e( 'Uncategorized', 'sdms' );
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                // Afficher la date de mise à jour
                                echo get_the_modified_date();
                                ?>
                            </td>
                            <td>
                                <?php
                                // Afficher les drapeaux avec les liens pour voir les documents

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
                                            $flag_url = $language['flag'];

                                            // Afficher l'image du drapeau avec le lien
                                            echo '<a href="' . esc_url( $download_url ) . '" target="_blank">';
                                            echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['lang'] ) . '" class="sdms-flag-icon">';
                                            echo '</a>';
                                        }
                                    }
                                    echo '</div>';
                                } else {
                                    echo '<p>' . __( 'Aucun fichier disponible.', 'sdms' ) . '</p>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                // Bouton pour envoyer le document
                                ?>
                                <button class="sdms-send-document-button" data-post-id="<?php the_ID(); ?>">
                                    <?php _e( 'Envoyer le document', 'sdms' ); ?>
                                </button>
                            </td>
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

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
?>
