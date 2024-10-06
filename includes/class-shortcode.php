<?php
/**
 * Class QTF_Shortcode
 * Registers and renders the questionnaire shortcode.
 */

class QTF_Shortcode {
    public static function init() {
        add_shortcode( 'qtf_questionnaire', array( __CLASS__, 'render' ) );
    }

    public static function render() {
        ob_start();
        ?>
        <div class="questionnaire-container">
            <h1 class="questionnaire-title">Product Recommendation Questionnaire</h1>
            <form id="qtf-questionnaire-form">
                <div id="qtf-questionnaire-content">
                    <!-- Dynamic Questions will be loaded here -->
                </div>
                <div class="navigation-buttons">
                    <button type="button" id="qtf-prev-btn" class="btn btn-secondary" style="display: none;">Previous</button>
                    <button type="button" id="qtf-next-btn" class="btn btn-primary">Next</button>
                    <button type="submit" id="qtf-submit-btn" class="btn btn-primary" style="display: none;">Submit</button>
                </div>
                <div id="qtf-step-indicator" class="step-indicator"></div>
            </form>
        </div>
        <div id="qtf-results-container" class="results-container" style="display: none;"></div>
        <?php
        return ob_get_clean();
    }
}
