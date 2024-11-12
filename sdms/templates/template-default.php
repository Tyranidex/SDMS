<?php
/*
Template Name: Default Template
*/

get_header();

// Step 1: Retrieve the selected file type for the document
$file_type = get_post_meta( get_the_ID(), '_sdms_file_type_image', true );

// Step 2: Initialize icon URL
$icon_url = '';

// Step 3: Proceed only if a file type is selected
if ( ! empty( $file_type ) ) {
    // Get custom icons from plugin options
    $file_type_icons = get_option( 'sdms_file_type_icons', array() );

    // Determine the icon URL
    if ( isset( $file_type_icons[ $file_type ] ) && ! empty( $file_type_icons[ $file_type ] ) ) {
        $icon_url = $file_type_icons[ $file_type ];
    } else {
        // Use default icon if custom icon is not set
        $default_icon_path = sdms_PLUGIN_DIR . 'assets/images/icons/' . $file_type . '.png';
        if ( file_exists( $default_icon_path ) ) {
            $icon_url = sdms_PLUGIN_URL . 'assets/images/icons/' . $file_type . '.png';
        }
    }
}

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        ?>
        <div class="sdms-document">
            <h1>
                <?php
                // Display the icon next to the document title if available
                if ( ! empty( $icon_url ) ) {
                    echo '<img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $file_type ) . ' icon" class="sdms-file-type-icon">';
                }
                the_title();
                ?>
            </h1>

            <div class="sdms-content">
                <?php
                // Display the content of the document
                the_content();
                ?>
            </div>

            <div class="sdms-download-links">
                <h2><?php _e( 'Download Files:', 'sdms' ); ?></h2>
                <ul>
                    <?php
                    // Get available languages from settings
                    $languages = get_option( 'sdms_languages', array() );
                    if ( is_array( $languages ) ) {
                        foreach ( $languages as $code => $language ) {
                            // Check if a file is uploaded for this language
                            $file_id = get_post_meta( get_the_ID(), 'sdms_file_' . $code, true );
                            if ( $file_id ) {
                                // Generate the download URL
                                $download_url = trailingslashit( get_permalink() ) . 'download/' . $code;

                                // Determine the flag URL
                                $flag_url = '';
                                if ( isset( $language['custom_flag'] ) && ! empty( $language['custom_flag'] ) ) {
                                    $flag_url = $language['custom_flag'];
                                } elseif ( ! empty( $language['flag'] ) ) {
                                    $flag_url = $language['flag'];
                                }

                                echo '<li>';
                                // Display the flag image if available
                                if ( ! empty( $flag_url ) ) {
                                    echo '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $language['country'] ) . ' flag">';
                                }
                                // Display the download link with the country name
                                echo '<a href="' . esc_url( $download_url ) . '">' . esc_html( $language['country'] ) . '</a>';
                                echo '</li>';
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();
