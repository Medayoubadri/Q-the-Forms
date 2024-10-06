<?php
/**
 * Class QTF_Enqueue
 * Handles enqueuing scripts and styles.
 */

class QTF_Enqueue {
    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function enqueue_assets() {
        if ( is_page_template( 'q-template.php' ) || has_shortcode( get_post()->post_content, 'qtf_questionnaire' ) ) {
            wp_enqueue_style( 'qtf-style', QTF_PLUGIN_URL . 'assets/css/questionnaire-style.css', array(), '1.1.0' );
            wp_enqueue_script( 'qtf-script', QTF_PLUGIN_URL . 'assets/js/questionnaire-script.js', array( 'jquery' ), '1.1.0', true );

            wp_localize_script( 'qtf-script', 'qtf_data', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'qtf_product_questionnaire' ),
            ) );
        }
    }
}
