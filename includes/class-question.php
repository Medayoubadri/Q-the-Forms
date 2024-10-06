<?php
/**
 * Class QTF_Question
 * Handles the registration of the 'Question' custom post type.
 */

class QTF_Question {
    public static function register() {
        $labels = array(
            'name'               => 'Questions',
            'singular_name'      => 'Question',
            'menu_name'          => 'Questions',
            'add_new_item'       => 'Add New Question',
            'edit_item'          => 'Edit Question',
            'new_item'           => 'New Question',
            'view_item'          => 'View Question',
            'search_items'       => 'Search Questions',
            'not_found'          => 'No questions found.',
            'not_found_in_trash' => 'No questions found in Trash.',
        );

        $args = array(
            'label'               => 'Questions',
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'public'              => false,
            'show_ui'             => true,
            'menu_icon'           => 'dashicons-editor-help',
            'hierarchical'        => false,
        );

        register_post_type( 'qtf_question', $args );
    }
}
