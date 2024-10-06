<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Render Admin Interface
function qtf_render_admin_interface() {
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'create_question';
    ?>
    <div class="wrap">
        <h1>Manage Questionnaire</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=qtf_manage_questionnaire&tab=create_question" class="nav-tab <?php echo $active_tab == 'create_question' ? 'nav-tab-active' : ''; ?>">Create Question</a>
            <a href="?page=qtf_manage_questionnaire&tab=manage_answers" class="nav-tab <?php echo $active_tab == 'manage_answers' ? 'nav-tab-active' : ''; ?>">Manage Answers</a>
            <a href="?page=qtf_manage_questionnaire&tab=define_paths" class="nav-tab <?php echo $active_tab == 'define_paths' ? 'nav-tab-active' : ''; ?>">Define Paths</a>
        </h2>

        <?php
        switch ( $active_tab ) {
            case 'create_question':
                qtf_render_create_question_tab();
                break;
            case 'manage_answers':
                qtf_render_manage_answers_tab();
                break;
            case 'define_paths':
                qtf_render_define_paths_tab();
                break;
        }
        ?>
    </div>
    <?php
}

// Functions for Admin Interface

// Render Create Question Tab
function qtf_render_create_question_tab() {
    if ( isset( $_POST['qtf_create_question'] ) ) {
        qtf_handle_create_question();
    }

    if ( isset( $_GET['question_id'] ) ) {
        $question_id = intval( $_GET['question_id'] );
        qtf_display_manage_answers( $question_id );
    } else {
        qtf_display_create_question_form();
    }
}

// Handle Create Question Form Submission
function qtf_handle_create_question() {
    // Verify nonce
    check_admin_referer( 'qtf_create_question_nonce' );

    $question_title     = sanitize_text_field( $_POST['question_title'] );
    $is_first_question  = isset( $_POST['is_first_question'] ) ? '1' : '';

    $question_id = wp_insert_post( array(
        'post_title'   => $question_title,
        'post_type'    => 'qtf_question',
        'post_status'  => 'publish',
    ) );

    if ( $question_id ) {
        update_post_meta( $question_id, '_qtf_is_first_question', $is_first_question );
        echo '<div class="notice notice-success is-dismissible"><p>Question created successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Failed to create question.</p></div>';
    }
}

// Display Create Question Form
function qtf_display_create_question_form() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field( 'qtf_create_question_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="question_title">Question Text</label></th>
                <td><input name="question_title" type="text" id="question_title" value="" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row">Is this the first question?</th>
                <td><input name="is_first_question" type="checkbox" id="is_first_question" value="1"></td>
            </tr>
        </table>
        <?php submit_button( 'Create Question', 'primary', 'qtf_create_question' ); ?>
    </form>

    <?php
    // List existing questions
    $questions = get_posts( array(
        'post_type' => 'qtf_question',
        'numberposts' => -1,
    ) );

    if ( $questions ) {
        echo '<h2>Existing Questions</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Question</th><th>Actions</th></tr></thead><tbody>';
        foreach ( $questions as $question ) {
            echo '<tr>';
            echo '<td>' . esc_html( $question->post_title ) . '</td>';
            echo '<td><a href="?page=qtf_manage_questionnaire&tab=create_question&question_id=' . $question->ID . '">Manage Answers</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}

// Display Manage Answers for a Question
function qtf_display_manage_answers( $question_id ) {
    $question = get_post( $question_id );
    if ( ! $question ) {
        echo '<div class="notice notice-error is-dismissible"><p>Invalid question ID.</p></div>';
        return;
    }

    echo '<h2>Managing Answers for: ' . esc_html( $question->post_title ) . '</h2>';

    // Handle answer creation
    if ( isset( $_POST['qtf_create_answer'] ) ) {
        qtf_handle_create_answer( $question_id );
    }

    // Display form to add new answer
    qtf_display_create_answer_form( $question_id );

    // List existing answers
    $answers = get_posts( array(
        'post_type' => 'qtf_answer',
        'numberposts' => -1,
        'meta_key' => '_qtf_associated_question',
        'meta_value' => $question_id,
    ) );

    if ( $answers ) {
        echo '<h3>Existing Answers</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Answer</th><th>Next Question</th><th>Associated Products</th><th>Actions</th></tr></thead><tbody>';
        foreach ( $answers as $answer ) {
            $next_question_id = get_post_meta( $answer->ID, '_qtf_next_question', true );
            $next_question = $next_question_id ? get_the_title( $next_question_id ) : 'End';
            $associated_products = get_post_meta( $answer->ID, '_qtf_associated_products', true );
            $product_names = array();
            if ( $associated_products ) {
                foreach ( $associated_products as $product_id ) {
                    $product_names[] = get_the_title( $product_id );
                }
            }
            echo '<tr>';
            echo '<td>' . esc_html( $answer->post_title ) . '</td>';
            echo '<td>' . esc_html( $next_question ) . '</td>';
            echo '<td>' . esc_html( implode( ', ', $product_names ) ) . '</td>';
            echo '<td>Edit | Delete</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}

// Handle Create Answer Form Submission
function qtf_handle_create_answer( $question_id ) {
    check_admin_referer( 'qtf_create_answer_nonce' );

    $answer_title        = sanitize_text_field( $_POST['answer_title'] );
    $next_question_input = $_POST['next_question'];
    $associated_products = isset( $_POST['associated_products'] ) ? array_map( 'intval', $_POST['associated_products'] ) : array();

    // Handle new question creation
    if ( $next_question_input === 'new_question' && ! empty( $_POST['new_question_title'] ) ) {
        $new_question_title = sanitize_text_field( $_POST['new_question_title'] );
        $next_question_id = wp_insert_post( array(
            'post_title'   => $new_question_title,
            'post_type'    => 'qtf_question',
            'post_status'  => 'publish',
        ) );
    } else {
        $next_question_id = intval( $next_question_input );
    }

    $answer_id = wp_insert_post( array(
        'post_title'   => $answer_title,
        'post_type'    => 'qtf_answer',
        'post_status'  => 'publish',
    ) );

    if ( $answer_id ) {
        update_post_meta( $answer_id, '_qtf_associated_question', $question_id );
        update_post_meta( $answer_id, '_qtf_next_question', $next_question_id );
        update_post_meta( $answer_id, '_qtf_associated_products', $associated_products );
        echo '<div class="notice notice-success is-dismissible"><p>Answer created successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Failed to create answer.</p></div>';
    }
}

// Display Create Answer Form
function qtf_display_create_answer_form( $question_id ) {
    $questions = get_posts( array(
        'post_type' => 'qtf_question',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ) );

    // Get products if WooCommerce is active
    $products = class_exists( 'WooCommerce' ) ? wc_get_products( array( 'limit' => -1 ) ) : array();

    ?>
    <h3>Add New Answer</h3>
    <form method="post" action="">
        <?php wp_nonce_field( 'qtf_create_answer_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="answer_title">Answer Text</label></th>
                <td><input name="answer_title" type="text" id="answer_title" value="" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="next_question">Next Question</label></th>
                <td>
                    <select name="next_question" id="next_question">
                        <option value="">-- End Questionnaire --</option>
                        <?php foreach ( $questions as $question ) : ?>
                            <?php if ( $question->ID != $question_id ) : // Exclude current question ?>
                                <option value="<?php echo esc_attr( $question->ID ); ?>"><?php echo esc_html( $question->post_title ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <option value="new_question">-- Add New Question --</option>
                    </select>
                </td>
            </tr>
            <tr id="new_question_row" style="display: none;">
                <th scope="row"><label for="new_question_title">New Question Text</label></th>
                <td><input name="new_question_title" type="text" id="new_question_title" value="" class="regular-text"></td>
            </tr>
            <?php if ( $products ) : ?>
            <tr>
                <th scope="row"><label for="associated_products">Associated Products</label></th>
                <td>
                    <select name="associated_products[]" id="associated_products" multiple style="width: 100%;">
                        <?php foreach ( $products as $product ) : ?>
                            <option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_name() ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple products.</p>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php submit_button( 'Add Answer', 'primary', 'qtf_create_answer' ); ?>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const nextQuestionSelect = document.getElementById('next_question');
        const newQuestionRow = document.getElementById('new_question_row');
        nextQuestionSelect.addEventListener('change', function() {
            if (this.value === 'new_question') {
                newQuestionRow.style.display = '';
            } else {
                newQuestionRow.style.display = 'none';
            }
        });
    });
    </script>
    <?php
}

// Render Manage Answers Tab
function qtf_render_manage_answers_tab() {
    // List all answers with options to edit or delete
    $answers = get_posts( array(
        'post_type' => 'qtf_answer',
        'numberposts' => -1,
    ) );

    if ( $answers ) {
        echo '<h2>All Answers</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Answer</th><th>Associated Question</th><th>Next Question</th><th>Actions</th></tr></thead><tbody>';
        foreach ( $answers as $answer ) {
            $associated_question_id = get_post_meta( $answer->ID, '_qtf_associated_question', true );
            $associated_question = get_the_title( $associated_question_id );
            $next_question_id = get_post_meta( $answer->ID, '_qtf_next_question', true );
            $next_question = $next_question_id ? get_the_title( $next_question_id ) : 'End';
            echo '<tr>';
            echo '<td>' . esc_html( $answer->post_title ) . '</td>';
            echo '<td>' . esc_html( $associated_question ) . '</td>';
            echo '<td>' . esc_html( $next_question ) . '</td>';
            echo '<td>Edit | Delete</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No answers found.</p>';
    }
}

// Render Define Paths Tab
function qtf_render_define_paths_tab() {
    // Display the question and answer paths
    $questions = get_posts( array(
        'post_type' => 'qtf_question',
        'numberposts' => -1,
    ) );

    if ( $questions ) {
        echo '<h2>Questionnaire Flow</h2>';
        foreach ( $questions as $question ) {
            echo '<h3>' . esc_html( $question->post_title ) . '</h3>';
            $answers = get_posts( array(
                'post_type' => 'qtf_answer',
                'numberposts' => -1,
                'meta_key' => '_qtf_associated_question',
                'meta_value' => $question->ID,
            ) );
            if ( $answers ) {
                echo '<ul>';
                foreach ( $answers as $answer ) {
                    $next_question_id = get_post_meta( $answer->ID, '_qtf_next_question', true );
                    $next_question = $next_question_id ? get_the_title( $next_question_id ) : 'End';
                    echo '<li>';
                    echo '<strong>' . esc_html( $answer->post_title ) . '</strong> leads to <em>' . esc_html( $next_question ) . '</em>';
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No answers for this question.</p>';
            }
        }
    } else {
        echo '<p>No questions found.</p>';
    }
}
