<?php 
if ( ! function_exists( 'sdms_get_available_languages' ) ) {
    /**
     * Récupérer les langues disponibles depuis le fichier Languages.json
     *
     * @return array
     */
    function sdms_get_available_languages() {
        $json_file = sdms_PLUGIN_DIR . 'assets/data/languages.json';
        if ( file_exists( $json_file ) ) {
            $json_data = file_get_contents( $json_file );
            $languages = json_decode( $json_data, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                return $languages;
            } else {
                // Gérer l'erreur JSON
                return array();
            }
        } else {
            // Fichier non trouvé
            return array();
        }
    }
}
 ?>