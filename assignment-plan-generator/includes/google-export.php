<?php
/**
 * Google Forms Export functionality for Assignment Generator
 * Handles creation and sharing of Google Forms from generated assignments
 * UPDATED: Fixed reverse order issue and added support for Long Answer and Give Reason question types
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ================ Register Google Forms export AJAX handler ================
add_action('wp_ajax_export_assignment_to_google_form', 'handle_export_assignment_to_google_form');
add_action('wp_ajax_nopriv_export_assignment_to_google_form', 'handle_export_assignment_to_google_form');

// ================ Main Google Forms export handler ================
function handle_export_assignment_to_google_form() {
    try {
        error_log('🚀Starting Google Form export handler...');
        
        // ================ Check Google API Client availability ================
        if (!class_exists('Google\Client')) {
            throw new Exception('Google API Client library not found.');
        }

        // ================ Security check with nonce verification ================
        if (!check_ajax_referer('assignment_generator_nonce', 'security', false)) {
            throw new Exception('Security check failed.');
        }

        // ================ Retrieve and sanitize POST data ================
        $assignment_title = isset($_POST['assignment_title']) ? sanitize_text_field($_POST['assignment_title']) : '';
        $assignment_content = isset($_POST['assignment_content']) ? wp_kses_post($_POST['assignment_content']) : '';
        $questions = isset($_POST['questions']) ? json_decode(stripslashes($_POST['questions']), true) : array();
        $grade = isset($_POST['grade']) ? sanitize_text_field($_POST['grade']) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';

        error_log('📊 Received data - Questions count: ' . count($questions));
        error_log('📝 Assignment title: ' . $assignment_title);

        // ================ Validate input data ================
        if (empty($assignment_title) || empty($questions)) {
            throw new Exception('Invalid data received - missing title or questions');
        }

        // Ensure that $questions is an array
        if (!is_array($questions)) {
            throw new Exception('Failed to decode questions data. Please try again.');
        }

        error_log('✅ Validation passed. Processing ' . count($questions) . ' questions');

        // ================ Initialize Google Client with Service Account ================
        $client = get_google_client_for_assignment();

        // ================ Create Google Forms service ================
        $service = new Google\Service\Forms($client);
        
        // Create form with title initially
        $form = new Google\Service\Forms\Form([
            'info' => [
                'title' => $assignment_title . ' (' . $grade . ' - ' . $subject . ')'
            ]
        ]);
        $form = $service->forms->create($form);
        
        error_log('📝 Created Google Form with ID: ' . $form->formId);

        // ================ FIXED: Add questions in correct order (not reversed) ================
        $questions_added = 0;
        $total_questions = count($questions);
        
        // Process questions in their original order, but we need to insert them at the correct position
        foreach ($questions as $index => $question) {
            if (empty($question['question']) || empty($question['type'])) {
                error_log('⚠️ Skipping invalid question at index ' . $index);
                continue;
            }

            // Clean question text to remove newlines and extra formatting
            $cleanedQuestion = cleanQuestionTextForGoogleForms($question['question']);
            
            error_log('📋 Processing question ' . ($index + 1) . ': ' . substr($cleanedQuestion, 0, 50) . '... (Type: ' . $question['type'] . ')');

            $item = null;

            // ================ Create different question types for Google Forms ================
            switch ($question['type']) {
                case 'multiple_choice':
                    if (isset($question['options']) && is_array($question['options'])) {
                        $options = [];
                        foreach ($question['options'] as $optionText) {
                            if (!empty($optionText)) {
                                // Clean option text as well
                                $cleanedOption = cleanQuestionTextForGoogleForms($optionText);
                                $options[] = ['value' => $cleanedOption];
                            }
                        }
                        
                        if (count($options) > 0) {
                            $item = new Google\Service\Forms\Item([
                                'title' => $cleanedQuestion,
                                'questionItem' => [
                                    'question' => [
                                        'required' => true,
                                        'choiceQuestion' => [
                                            'type' => 'RADIO',
                                            'options' => $options,
                                            'shuffle' => true
                                        ]
                                    ]
                                ]
                            ]);
                            error_log('✅ Created multiple choice with ' . count($options) . ' options');
                        }
                    } else {
                        // Fallback: treat as short answer if no valid options
                        $item = new Google\Service\Forms\Item([
                            'title' => $cleanedQuestion,
                            'questionItem' => [
                                'question' => [
                                    'required' => true,
                                    'textQuestion' => [
                                        'paragraph' => false
                                    ]
                                ]
                            ]
                        ]);
                        error_log('⚠️ No valid options found, converting to text question');
                    }
                    break;

                case 'true_false':
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'choiceQuestion' => [
                                    'type' => 'RADIO',
                                    'options' => [
                                        ['value' => 'True'],
                                        ['value' => 'False']
                                    ],
                                    'shuffle' => false
                                ]
                            ]
                        ]
                    ]);
                    error_log('✅ Created true/false question');
                    break;

                case 'short_answer':
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'textQuestion' => [
                                    'paragraph' => false
                                ]
                            ]
                        ]
                    ]);
                    error_log('✅ Created short answer question');
                    break;

                case 'long_answer':
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'textQuestion' => [
                                    'paragraph' => true
                                ]
                            ]
                        ]
                    ]);
                    error_log('✅ Created long answer question (paragraph style)');
                    break;

                case 'give_reason':
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'description' => 'Please provide a detailed explanation with your reasoning.',
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'textQuestion' => [
                                    'paragraph' => true
                                ]
                            ]
                        ]
                    ]);
                    error_log('✅ Created give reason question (paragraph style with description)');
                    break;

                case 'fill_in_the_blank':
                case 'fill_blanks':
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'textQuestion' => [
                                    'paragraph' => false
                                ]
                            ]
                        ]
                    ]);
                    error_log('✅ Created fill in the blank question');
                    break;

                default:
                    // For any other question type, treat as short answer
                    $item = new Google\Service\Forms\Item([
                        'title' => $cleanedQuestion,
                        'questionItem' => [
                            'question' => [
                                'required' => true,
                                'textQuestion' => [
                                    'paragraph' => true
                                ]
                            ]
                        ]
                    ]);
                    error_log('⚠️ Unknown question type: ' . $question['type'] . ', treating as short answer');
                    break;
            }

            // ================ FIXED: Add each question at the END to maintain order ================
            if ($item !== null) {
                $request = new Google\Service\Forms\Request([
                    'createItem' => [
                        'item' => $item,
                        'location' => ['index' => $questions_added] // Insert at the correct position
                    ]
                ]);

                // Send individual request for each question
                $service->forms->batchUpdate($form->formId, new Google\Service\Forms\BatchUpdateFormRequest([
                    'requests' => [$request]
                ]));
                
                $questions_added++;
                error_log('📋 Added question ' . ($index + 1) . ' to form at position ' . $questions_added);
            } else {
                error_log('❌ Failed to create item for question at index ' . $index);
            }
        }

        // ================ Share the form publicly with edit access ================
        $driveService = new Google\Service\Drive($client);

        // Create a permission for anyone to access
        $permissions = new Google\Service\Drive\Permission([
            'type' => 'anyone',
            'role' => 'writer'
        ]);
        
        // Share the form with edit access
        $driveService->permissions->create($form->formId, $permissions, ['sendNotificationEmail' => false]);
        error_log('🔓 Form shared publicly with edit access');

        // ================ Return success response with form URL ================
        $form_url = 'https://docs.google.com/forms/d/' . $form->formId . '/edit';
        error_log('🎉 Form creation completed successfully: ' . $form_url);
        error_log('📊 Questions added in correct order: ' . $questions_added . ' out of ' . $total_questions);
        
        wp_send_json_success([
            'form_url' => $form_url,
            'questions_added' => $questions_added,
            'form_id' => $form->formId,
            'total_questions' => $total_questions
        ]);

    } catch (Exception $e) {
        error_log('❌ Assignment Generator Google Form Export Error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

// ================ Clean question text for Google Forms compatibility ================
function cleanQuestionTextForGoogleForms($text) {
    // Remove HTML tags
    $cleaned = strip_tags($text);
    
    // Remove newlines, carriage returns, and extra whitespace
    $cleaned = str_replace(["\n", "\r", "\t"], ' ', $cleaned);
    
    // Replace multiple spaces with single space
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
    
    // Trim whitespace from beginning and end
    $cleaned = trim($cleaned);
    
    // Limit length if too long (Google Forms has limits)
    if (strlen($cleaned) > 1000) {
        $cleaned = substr($cleaned, 0, 997) . '...';
    }
    
    return $cleaned;
}

// ================ Get Google API Client with service account authentication ================
function get_google_client_for_assignment() {
    if (!class_exists('Google\Client')) {
        throw new Exception('Google API Client library not found. Please ensure it is installed correctly.');
    }

    $client = new Google\Client();
    
    // ================ Try to find service account JSON file ================
    $service_account_paths = [
        plugin_dir_path(__FILE__) . '../../youtube-video-quiz-generator/service-account.json',
        plugin_dir_path(__FILE__) . '../service-account.json'
    ];
    
    $service_account_json_path = null;
    foreach ($service_account_paths as $path) {
        if (file_exists($path)) {
            $service_account_json_path = $path;
            break;
        }
    }

    if (!$service_account_json_path) {
        throw new Exception('Service Account JSON file not found. Please ensure the file is located in the plugin directory or YouTube Quiz Generator plugin directory.');
    }

    // ================ Set Google API credentials and scopes ================
    $client->setAuthConfig($service_account_json_path);

    // Set the required scopes for Google Drive and Forms
    $client->addScope('https://www.googleapis.com/auth/drive');
    $client->addScope('https://www.googleapis.com/auth/forms');
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    return $client;
}