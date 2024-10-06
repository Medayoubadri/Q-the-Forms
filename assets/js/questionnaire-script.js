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
            data: {
                action: 'qtf_get_next_question',
                nonce: qtf_data.nonce,
                current_question_id: currentQuestionId,
                selected_answer_id: answersGiven[currentQuestionId] || 0,
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
        if (previousQuestions.length > 0) {
            $('#qtf-prev-btn').show();
        } else {
            $('#qtf-prev-btn').hide();
        }

        // Determine if the current question is the last one
        // For simplicity, assume if there are no more questions, show Submit
        // Otherwise, show Next
        // This logic might need to be adjusted based on your questionnaire flow
        $('#qtf-next-btn').show();
        $('#qtf-submit-btn').hide();
    }

    // Initial load
    loadQuestion(currentQuestionId);
    updateStepIndicator();
    toggleNavigationButtons();

    // Handle Next Button
    $('#qtf-next-btn').on('click', function() {
        let selectedAnswer = $('input[name="answer"]:checked').val();
        if (!selectedAnswer) {
            alert('Please select an option before proceeding.');
            return;
        }

        answersGiven[currentQuestionId] = selectedAnswer;

        // Push the current question ID to previousQuestions before loading the next one
        previousQuestions.push(currentQuestionId);

        loadQuestion(currentQuestionId);
    });

    // Handle Previous Button
    $('#qtf-prev-btn').on('click', function() {
        if (previousQuestions.length === 0) return; // No previous question to go back to

        // Remove the last question ID from previousQuestions
        let lastQuestionId = previousQuestions.pop();

        // Set currentQuestionId to the previous question
        if (previousQuestions.length > 0) {
            currentQuestionId = previousQuestions[previousQuestions.length - 1];
        } else {
            currentQuestionId = 0; // Reset to first question if no previous questions
        }

        // Remove the answer for the current question
        delete answersGiven[lastQuestionId];

        // Load the previous question
        loadQuestion(currentQuestionId);
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
        previousQuestions.push(currentQuestionId);
        loadQuestion(currentQuestionId);
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
        toggleNavigationButtons();
        loadQuestion(currentQuestionId);
    });
});
