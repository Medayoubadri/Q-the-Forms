<?php
/**
 * Class QTF_Ajax_Handler
 * Handles AJAX requests for the questionnaire.
 */

class QTF_Ajax_Handler {
    public static function init() {
        add_action( 'wp_ajax_qtf_get_next_question', array( __CLASS__, 'get_next_question' ) );
        add_action( 'wp_ajax_nopriv_qtf_get_next_question', array( __CLASS__, 'get_next_question' ) );
        add_action( 'wp_ajax_qtf_process_questionnaire', array( __CLASS__, 'process_questionnaire' ) );
        add_action( 'wp_ajax_nopriv_qtf_process_questionnaire', array( __CLASS__, 'process_questionnaire' ) );
    }

    public static function get_next_question() {
        check_ajax_referer( 'qtf_product_questionnaire', 'nonce' );

        $current_question_id = isset( $_POST['current_question_id'] ) ? intval( $_POST['current_question_id'] ) : 0;
        $selected_answer_id = isset( $_POST['selected_answer_id'] ) ? intval( $_POST['selected_answer_id'] ) : 0;
        $previous_answers = isset( $_POST['previous_answers'] ) ? $_POST['previous_answers'] : array();

        if ( $current_question_id === 0 ) {
            $first_question = get_posts( array(
                'post_type'      => 'qtf_question',
                'posts_per_page' => 1,
                'meta_key'       => '_qtf_is_first_question',
                'meta_value'     => '1',
            ) );

            if ( empty( $first_question ) ) {
                wp_send_json_error( 'No first question set.' );
                wp_die();
            }

            $question = $first_question[0];
        } else {
            // Get the selected answer to determine the next question
            if ( ! empty( $selected_answer_id ) ) {
                $answer = get_post( $selected_answer_id );
                if ( ! $answer ) {
                    wp_send_json_error( 'Invalid answer selected.' );
                    wp_die();
                }
            }
            $next_question_id = get_post_meta( $answer->ID, '_qtf_next_question', true );

            if ( ! $next_question_id ) {
                // No next question, end of questionnaire
                wp_send_json_success( array(
                    'action' => 'end_questionnaire',
                ) );
                wp_die();
            }

            $question = get_post( $next_question_id );
            if ( ! $question ) {
                wp_send_json_error( 'Next question not found.' );
                wp_die();
            }
        }

        // Get question details
        $question_text = $question->post_title;
        $answers = get_posts( array(
            'post_type'      => 'qtf_answer',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_qtf_associated_question',
                    'value'   => $question->ID,
                    'compare' => '=',
                ),
            ),
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( empty( $answers ) ) {
            wp_send_json_error( 'No answers found for this question.' );
            wp_die();
        }

        // Generate HTML for the question and answers
        ob_start();
        ?>
        <div class="step" id="question-<?php echo esc_attr( $question->ID ); ?>">
            <h2 class="step-title"><?php echo esc_html( $question_text ); ?></h2>
            <div class="question">
                <?php foreach ( $answers as $answer ) : ?>
                    <label class="option">
                        <input type="radio" name="answer" value="<?php echo esc_attr( $answer->ID ); ?>">
                        <span class="option-text"><?php echo esc_html( $answer->post_title ); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();

        wp_send_json_success( array(
            'action'      => 'next_question',
            'question_id' => $question->ID,
            'html'        => $html,
        ) );
        wp_die();
    }

    public static function process_questionnaire() {
        check_ajax_referer( 'qtf_product_questionnaire', 'nonce' );

        $answers = isset( $_POST['answers'] ) ? array_map( 'intval', $_POST['answers'] ) : array();

        if ( empty( $answers ) ) {
            wp_send_json_error( 'No answers provided.' );
            wp_die();
        }

        // Collect associated products from the selected answers
        $product_ids = array();

        foreach ( $answers as $answer_id ) {
            $associated_products = get_post_meta( $answer_id, '_qtf_associated_products', true );
            if ( ! empty( $associated_products ) && is_array( $associated_products ) ) {
                foreach ( $associated_products as $product_id ) {
                    $product_ids[] = $product_id;
                }
            }
        }

        // Remove duplicate product IDs
        $product_ids = array_unique( $product_ids );

        if ( empty( $product_ids ) ) {
            wp_send_json_error( 'No products associated with the selected answers.' );
            wp_die();
        }

        // Fetch the products
        $products = wc_get_products( array(
            'include' => $product_ids,
            'limit'   => -1,
        ) );

        if ( empty( $products ) ) {
            wp_send_json_error( 'No products found.' );
            wp_die();
        }

        // Generate HTML for the product recommendations
        ob_start();
        ?>
        <h2 class="results-title">Your Personalized Recommendations</h2>
        <div class="results-summary">
            <h3>Based on your answers:</h3>
            <ul>
                <?php
                foreach ( $answers as $answer_id ) {
                    $answer = get_post( $answer_id );
                    if ( $answer ) {
                        // Get the associated question
                        $question_id = get_post_meta( $answer_id, '_qtf_associated_question', true );
                        $question = get_post( $question_id );
                        if ( $question ) {
                            echo '<li>' . esc_html( $question->post_title ) . ': ' . esc_html( $answer->post_title ) . '</li>';
                        }
                    }
                }
                ?>
            </ul>
        </div>
        <div class="product-grid">
            <?php foreach ( $products as $product ) : ?>
                <div class="product-card">
                    <a href="<?php echo get_permalink( $product->get_id() ); ?>">
                        <img src="<?php echo wp_get_attachment_url( $product->get_image_id() ); ?>" class="product-image" alt="<?php echo esc_attr( $product->get_name() ); ?>">
                        <h4 class="product-name"><?php echo esc_html( $product->get_name() ); ?></h4>
                    </a>
                    <p class="product-description"><?php echo wp_trim_words( $product->get_description(), 15 ); ?></p>
                    <a href="<?php echo get_permalink( $product->get_id() ); ?>" class="product-link">View Product</a>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="#" id="qtf-retake-questionnaire" class="retake-button">Retake Questionnaire</a>
        <?php
        $html = ob_get_clean();

        wp_send_json_success( array(
            'html' => $html,
        ) );
        wp_die();
    }
}
