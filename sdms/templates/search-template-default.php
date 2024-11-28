<?php
/**
 * Template Name: Search Template Default
 *
 * Description: The default template for search results of sdms_document.
 */

add_filter( 'body_class', function( $classes ) {
    $classes[] = 'sdms-search-template-default';
    return $classes;
} );

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <h1><?php printf( __( 'Search Results for: %s', 'sdms' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>

        <div class="sdms-archive-container">
            <!-- Barre latérale des catégories -->
            <aside class="sdms-sidebar">
                <h2><?php esc_html_e( 'Catégories', 'sdms' ); ?></h2>
                <ul class="sdms-category-list">
                    <li><a href="<?php echo esc_url( get_post_type_archive_link( 'sdms_document' ) ); ?>"><?php esc_html_e( 'Afficher tout', 'sdms' ); ?></a></li>
                    <?php
                    $categories = get_terms( array(
                        'taxonomy'   => 'sdms_category',
                        'hide_empty' => true,
                    ) );

                    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                        foreach ( $categories as $category ) {
                            echo '<li><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a></li>';
                        }
                    } else {
                        echo '<li>' . esc_html__( 'Aucune catégorie trouvée.', 'sdms' ) . '</li>';
                    }
                    ?>
                </ul>
            </aside>

            <!-- Contenu principal -->
            <div class="sdms-main-content">
                <!-- Formulaire de recherche -->
                <form id="sdms-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="hidden" name="post_type" value="sdms_document">
                    <input type="text" id="sdms-search-input" name="s" placeholder="<?php esc_attr_e( 'Rechercher des documents...', 'sdms' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
                    <button type="submit"><?php esc_html_e( 'Rechercher', 'sdms' ); ?></button>
                </form>

                <!-- Conteneur pour les résultats -->
                <div id="sdms-search-results">
                    <?php if ( have_posts() ) : ?>
                        <table class="sdms-document-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Document', 'sdms' ); ?></th>
                                    <th><?php esc_html_e( 'Description', 'sdms' ); ?></th>
                                    <th><?php esc_html_e( 'Categories', 'sdms' ); ?></th>
                                    <th><?php esc_html_e( 'Last Updated', 'sdms' ); ?></th>
                                    <th><?php esc_html_e( 'Languages', 'sdms' ); ?></th>
                                    <th><?php esc_html_e( 'Share', 'sdms' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ( have_posts() ) : the_post(); ?>
                                    <tr>
                                        <td>
                                            <?php
                                            // Afficher l'icône du type de fichier
                                            $file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );
                                            $icon_url  = sdms_get_file_type_icon_url( $file_type );

                                            if ( $icon_url ) {
                                                echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . '" class="sdms-file-type-icon">';
                                            }
                                            ?>
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        </td>
                                        <td><?php the_excerpt(); ?></td>
                                        <td>
                                            <?php
                                            $terms = get_the_terms( get_the_ID(), 'sdms_category' );
                                            if ( $terms && ! is_wp_error( $terms ) ) {
                                                $categories = array();
                                                foreach ( $terms as $term ) {
                                                    $categories[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
                                                }
                                                echo implode( ', ', $categories );
                                            } else {
                                                esc_html_e( 'Uncategorized', 'sdms' );
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo esc_html( get_the_modified_date() ); ?></td>
                                        <td><?php sdms_display_language_links( get_the_ID() ); ?></td>
                                        <td>
                                            <button class="sdms-send-document-button" data-post-id="<?php the_ID(); ?>">
                                                <?php esc_html_e( 'Send Document', 'sdms' ); ?>
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
                                'mid_size'  => 2,
                                'prev_text' => __( 'Previous', 'sdms' ),
                                'next_text' => __( 'Next', 'sdms' ),
                            ) );
                            ?>
                        </div>
                    <?php else : ?>
                        <p><?php esc_html_e( 'No documents found.', 'sdms' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main><!-- #main -->
    <script>
        alert("ok");
    </script>
</div><!-- #primary -->

<?php
get_footer();
?>
