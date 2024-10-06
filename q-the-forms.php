<?php
/**
 * Plugin Name: Q the Forms
 * Description: A dynamic plugin to handle product recommendation questionnaires, manage product tags and slugs, and allow easy management of questionnaire steps and questions.
 * Version: 1.1.5 (Beta)
 * Author: MedAyouBadri
 * Text Domain: q-the-forms
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define Plugin Constants
define( 'QTF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'QTF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Required Files
require_once QTF_PLUGIN_DIR . 'includes/class-question.php';
require_once QTF_PLUGIN_DIR . 'includes/class-answer.php';
require_once QTF_PLUGIN_DIR . 'includes/class-meta-boxes.php';
require_once QTF_PLUGIN_DIR . 'includes/class-ajax-handler.php';
require_once QTF_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once QTF_PLUGIN_DIR . 'includes/class-enqueue.php';

// Initialize Plugin
function qtf_init() {
    // Register Custom Post Types
    add_action( 'init', array( 'QTF_Question', 'register' ) );
    add_action( 'init', array( 'QTF_Answer', 'register' ) );

    // Initialize Meta Boxes, AJAX Handlers, Shortcodes, etc.
    QTF_Meta_Boxes::init();
    QTF_Ajax_Handler::init();
    QTF_Shortcode::init();
    QTF_Enqueue::init();
}
add_action( 'plugins_loaded', 'qtf_init' );