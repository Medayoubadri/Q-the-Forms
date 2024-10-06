jQuery(document).ready(function($) {
    let currentQuestionId = 0; // Start with 0 to load the first question
    let previousQuestions = [];
    let answersGiven = {};

    function updateStepIndicator() {
        let totalSteps = previousQuestions.length + 1;
        $('#qtf-step-indicator').text(`Step ${totalSteps}`);
    }

    function loadQuestion(questionId) {
        $.ajax({
            url: qtf_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'qtf_get_next_question',
                nonce: qtf_data.nonce,
                current_question_id: currentQuestionId,
                selected_answer_id: answersGiven[currentQuestionId],
                previous_answers: answersGiven
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.action === 'end_questionnaire') {
                        // Submit the questionnaire
                        submitQuestionnaire();
                    } else if (response.data.action === 'next_question') {
                        $('#qtf-questionnaire-content').html(response.data.html);
                        currentQuestionId = response.data.question_id;
                        updateStepIndicator();
                        toggleNavigationButtons();
                    }
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while loading the question.');
            }
        });
    }

    function submitQuestionnaire() {
        $.ajax({
            url: qtf_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'qtf_process_questionnaire',
                nonce: qtf_data.nonce,
                answers: answersGiven
            },
            success: function(response) {
                if (response.success) {
                    $('#qtf-results-container').html(response.data.html).show();
                    $('#qtf-questionnaire-form').hide();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while processing the questionnaire.');
            }
        });
    }

    function toggleNavigationButtons() {
        if (previousQuestions.length > 1) {
            $('#qtf-prev-btn').show();
        } else {
            $('#qtf-prev-btn').hide();
        }

        if (currentQuestionId === 0 || $('#qtf-questionnaire-content').find('.step').length === 0 ){
            $('#qtf-next-btn').hide();
            $('#qtf-submit-btn').show();
        } else {
            $('#qtf-next-btn').show();
            $('#qtf-submit-btn').hide();
        }
    }

    // Initial load
    loadQuestion(currentQuestionId);

    // Handle Next Button
    $('#qtf-next-btn').on('click', function() {
        let selectedAnswer = $('input[name="answer"]:checked').val();
        if (!selectedAnswer) {
            alert('Please select an option before proceeding.');
            return;
        }

        answersGiven[currentQuestionId] = selectedAnswer;
        loadQuestion(currentQuestionId);
    });

    // Handle Previous Button
    $('#qtf-prev-btn').on('click', function() {
        if (previousQuestions.length < 2) return;

        // Remove the current question
        previousQuestions.pop();
        let prevQuestionId = previousQuestions[previousQuestions.length - 1];

        currentQuestionId = prevQuestionId;

        // Remove the answer for the current question
        delete answersGiven[currentQuestionId];

        // Load the previous question
        $.ajax({
            url: qtf_data.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'qtf_get_next_question',
                nonce: qtf_data.nonce,
                current_question_id: currentQuestionId,
                selected_answer_id: answersGiven[currentQuestionId],
                previous_answers: answersGiven
            },
            success: function(response) {
                if (response.success) {
                    $('#qtf-questionnaire-content').html(response.data.html);
                    updateStepIndicator();
                    toggleNavigationButtons();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while loading the question.');
            }
        });
    });

    // Handle Form Submission
    $('#qtf-questionnaire-form').on('submit', function(e) {
        e.preventDefault();
        let selectedAnswer = $('input[name="answer"]:checked').val();
        if (!selectedAnswer) {
            alert('Please select an option before submitting.');
            return;
        }

        answersGiven[currentQuestionId] = selectedAnswer;
        submitQuestionnaire();
    });

    // Handle Retake Questionnaire
    $(document).on('click', '#qtf-retake-questionnaire', function(e) {
        e.preventDefault();
        $('#qtf-results-container').hide();
        $('#qtf-questionnaire-form').show();
        $('#qtf-questionnaire-content').html('');
        previousQuestions = [];
        answersGiven = {};
        currentQuestionId = 0;
        updateStepIndicator();
        loadQuestion(currentQuestionId);
    });
});
