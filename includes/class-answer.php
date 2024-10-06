<?php
/**
 * Class QTF_Answer
 * Handles the registration of the 'Answer' custom post type.
 */

class QTF_Answer {
    public static function register() {
        $labels = array(
            'name'               => 'Answers',
            'singular_name'      => 'Answer',
            'menu_name'          => 'Answers',
            'add_new_item'       => 'Add New Answer',
            'edit_item'          => 'Edit Answer',
            'new_item'           => 'New Answer',
            'view_item'          => 'View Answer',
            'search_items'       => 'Search Answers',
            'not_found'          => 'No answers found.',
            'not_found_in_trash' => 'No answers found in Trash.',
        );

        $args = array(
            'label'               => 'Answers',
            'labels'              => $labels,
            'supports'            => array( 'title' ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'edit.php?post_type=qtf_question', // Nested under Questions
            'menu_icon'           => 'dashicons-editor-ul',
            'hierarchical'        => false,
        );

        register_post_type( 'qtf_answer', $args );
    }
}
