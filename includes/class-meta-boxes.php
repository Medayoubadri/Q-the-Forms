<?php
/**
 * Class QTF_Meta_Boxes
 * Handles adding meta boxes to Questions and Answers.
 */

// class QTF_Meta_Boxes {
//     public static function init() {
//         add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
//         add_action( 'save_post', array( __CLASS__, 'save_meta_boxes_data' ) );
//     }

//     public static function add_meta_boxes() {
//         // Meta box for Questions
//         add_meta_box(
//             'qtf_question_details',
//             'Question Details',
//             array( __CLASS__, 'render_question_meta_box' ),
//             'qtf_question',
//             'normal',
//             'default'
//         );

//         // Meta box for Answers
//         add_meta_box(
//             'qtf_answer_details',
//             'Answer Details',
//             array( __CLASS__, 'render_answer_meta_box' ),
//             'qtf_answer',
//             'normal',
//             'default'
//         );
//     }

//     public static function render_question_meta_box( $post ) {
//         wp_nonce_field( 'qtf_save_question_meta', 'qtf_question_meta_nonce' );
//         $is_first_question = get_post_meta( $post->ID, '_qtf_is_first_question', true );

//         echo '<p>';
//         echo '<label>';
//         echo '<input type="checkbox" name="qtf_is_first_question" value="1"' . checked( $is_first_question, '1', false ) . ' />';
//         echo ' Mark as the first question';
//         echo '</label>';
//         echo '</p>';
//     }

//     public static function render_answer_meta_box( $post ) {
//         wp_nonce_field( 'qtf_save_answer_meta', 'qtf_answer_meta_nonce' );

//         $associated_question = get_post_meta( $post->ID, '_qtf_associated_question', true );
//         $next_question = get_post_meta( $post->ID, '_qtf_next_question', true );
//         $associated_products = get_post_meta( $post->ID, '_qtf_associated_products', true );

//         // Get all questions
//         $questions = get_posts( array(
//             'post_type'      => 'qtf_question',
//             'posts_per_page' => -1,
//             'orderby'        => 'title',
//             'order'          => 'ASC',
//         ) );

//         // Associated Question
//         echo '<p>';
//         echo '<label for="qtf_associated_question">Associated Question</label><br />';
//         echo '<select id="qtf_associated_question" name="qtf_associated_question" required>';
//         echo '<option value="">-- Select a Question --</option>';
//         foreach ( $questions as $question_item ) {
//             echo '<option value="' . esc_attr( $question_item->ID ) . '"' . selected( $associated_question, $question_item->ID, false ) . '>' . esc_html( $question_item->post_title ) . '</option>';
//         }
//         echo '</select>';
//         echo '</p>';

//         // Next Question
//         echo '<p>';
//         echo '<label for="qtf_next_question">Next Question</label><br />';
//         echo '<select id="qtf_next_question" name="qtf_next_question">';
//         echo '<option value="">-- End Questionnaire --</option>';
//         foreach ( $questions as $question_item ) {
//             echo '<option value="' . esc_attr( $question_item->ID ) . '"' . selected( $next_question, $question_item->ID, false ) . '>' . esc_html( $question_item->post_title ) . '</option>';
//         }
//         echo '</select>';
//         echo '</p>';

//         // Get all products (requires WooCommerce)
//         if ( class_exists( 'WooCommerce' ) ) {
//             $products = wc_get_products( array( 'limit' => -1 ) );

//             // Associated Products
//             echo '<p>';
//             echo '<label for="qtf_associated_products">Associated Products</label><br />';
//             echo '<select id="qtf_associated_products" name="qtf_associated_products[]" multiple style="width:100%;">';
//             foreach ( $products as $product ) {
//                 echo '<option value="' . esc_attr( $product->get_id() ) . '"' . ( is_array( $associated_products ) && in_array( $product->get_id(), $associated_products ) ? ' selected' : '' ) . '>' . esc_html( $product->get_name() ) . '</option>';
//             }
//             echo '</select>';
//             echo '</p>';
//         } else {
//             echo '<p>Please install and activate WooCommerce to select associated products.</p>';
//         }
//     }

//     public static function save_meta_boxes_data( $post_id ) {
//         // Save Question Meta
//         if ( isset( $_POST['qtf_question_meta_nonce'] ) && wp_verify_nonce( $_POST['qtf_question_meta_nonce'], 'qtf_save_question_meta' ) ) {
//             $is_first_question = isset( $_POST['qtf_is_first_question'] ) ? '1' : '';
//             update_post_meta( $post_id, '_qtf_is_first_question', $is_first_question );
//         }

//         // Save Answer Meta
//         if ( isset( $_POST['qtf_answer_meta_nonce'] ) && wp_verify_nonce( $_POST['qtf_answer_meta_nonce'], 'qtf_save_answer_meta' ) ) {
//             $associated_question = isset( $_POST['qtf_associated_question'] ) ? intval( $_POST['qtf_associated_question'] ) : 0;
//             $next_question = isset( $_POST['qtf_next_question'] ) ? intval( $_POST['qtf_next_question'] ) : 0;
//             $associated_products = isset( $_POST['qtf_associated_products'] ) ? array_map( 'intval', $_POST['qtf_associated_products'] ) : array();

//             update_post_meta( $post_id, '_qtf_associated_question', $associated_question );
//             update_post_meta( $post_id, '_qtf_next_question', $next_question );
//             update_post_meta( $post_id, '_qtf_associated_products', $associated_products );
//         }
//     }
// }
