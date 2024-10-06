<?php
/**
 * Plugin Name: Q the Forms
 * Description: A plugin to dynamically manage product recommendation forms.
 * Version: 1.2.0
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

    // Add Admin Menu
    add_action( 'admin_menu', 'qtf_add_admin_menu' );
}
add_action( 'plugins_loaded', 'qtf_init' );

// Add Admin Menu
function qtf_add_admin_menu() {
    add_menu_page(
        'Q the Forms',                 // Page title
        'Q the Forms',                 // Menu title
        'manage_options',              // Capability
        'qtf_manage_questionnaire',    // Menu slug
        'qtf_render_admin_page',       // Callback function
        'dashicons-feedback',          // Icon
        6                              // Position
    );
}

// Render Admin Page
function qtf_render_admin_page() {
    // Include the admin interface file
    include QTF_PLUGIN_DIR . 'includes/admin-interface.php';
}
