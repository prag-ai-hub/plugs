<?php
/**
 * Plugin Name: Assignment Generator
 * Description: A plugin to generate educational assignments based on CBSE blueprint pattern with section-wise questions and marking scheme.
 * Version: 2.2
 * Author: Vidyahub
 * Updated: Fixed case study double display, added progress bar, fixed answer key
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('ASSIGNMENT_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ASSIGNMENT_GENERATOR_PLUGIN_PATH', plugin_dir_path(__FILE__));

// ================ Google API Client autoload setup ================
if (file_exists(plugin_dir_path(__FILE__) . '../youtube-video-quiz-generator/vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . '../youtube-video-quiz-generator/vendor/autoload.php';
} elseif (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
}

// ================ Include Google Export functionality ================
require_once ASSIGNMENT_GENERATOR_PLUGIN_PATH . 'includes/google-export.php';

// ================ CBSE Blueprint Configuration ================
function get_cbse_blueprint_config() {
    return array(
        'question_types' => array(
            'mcq' => array('marks' => 1, 'section' => 'A'),
            'fill_blanks' => array('marks' => 1, 'section' => 'A'),
            'true_false' => array('marks' => 1, 'section' => 'A'),
            'very_short_answer' => array('marks' => 1, 'section' => 'A'),
            'assertion_reason' => array('marks' => 1, 'section' => 'A'),
            'short_answer' => array('marks' => 2, 'section' => 'B'),
            'numerical' => array('marks' => 2, 'section' => 'B'),
            'give_reason' => array('marks' => 2, 'section' => 'B'),
            'short_answer_3' => array('marks' => 3, 'section' => 'C'),
            'numerical_3' => array('marks' => 3, 'section' => 'C'),
            'diagram_based' => array('marks' => 3, 'section' => 'C'),
            'long_answer' => array('marks' => 5, 'section' => 'D'),
            'case_study' => array('marks' => 4, 'section' => 'E'),
            'matrix_match' => array('marks' => 4, 'section' => 'D'),
            'source_based' => array('marks' => 4, 'section' => 'E'),
            'map_based' => array('marks' => 4, 'section' => 'E')
        )
    );
}

// ================ Subject-Specific Configuration ================
function get_subject_specific_config($subject) {
    $configs = array(
        'Mathematics' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'very_short_answer', 'short_answer', 'numerical', 'numerical_3', 'long_answer', 'case_study'),
            'special_instructions' => 'Include step-by-step solutions. For numerical problems, show all working.'
        ),
        'Science' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'true_false', 'assertion_reason', 'very_short_answer', 'short_answer', 'give_reason', 'diagram_based', 'long_answer', 'case_study'),
            'special_instructions' => 'Include diagrams where applicable. For Physics/Chemistry, include numerical problems.'
        ),
        'Social Studies' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'true_false', 'very_short_answer', 'short_answer', 'give_reason', 'source_based', 'map_based', 'long_answer', 'case_study'),
            'special_instructions' => 'Include source-based questions for History. Include map-based questions for Geography.'
        ),
        'Hindi' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'very_short_answer', 'short_answer', 'long_answer', 'case_study'),
            'special_instructions' => 'Include questions on grammar, comprehension, and literature. Use proper Hindi script.'
        ),
        'English' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'very_short_answer', 'short_answer', 'long_answer', 'case_study', 'source_based'),
            'special_instructions' => 'Include reading comprehension, grammar, writing skills, and literature sections.'
        ),
        'Marathi' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'very_short_answer', 'short_answer', 'long_answer', 'case_study'),
            'special_instructions' => 'Include questions on grammar, comprehension, and literature. Use proper Marathi script.'
        ),
        'Computer Science' => array(
            'enabled_types' => array('mcq', 'fill_blanks', 'true_false', 'very_short_answer', 'short_answer', 'numerical', 'long_answer', 'case_study'),
            'special_instructions' => 'Include programming questions, output prediction, error identification.'
        )
    );
    
    $default_config = array(
        'enabled_types' => array('mcq', 'fill_blanks', 'true_false', 'very_short_answer', 'short_answer', 'give_reason', 'long_answer', 'case_study'),
        'special_instructions' => 'Follow standard question paper pattern.'
    );
    
    return isset($configs[$subject]) ? $configs[$subject] : $default_config;
}

// ================ Register the shortcode ================
function assignment_generator_shortcode() {
    ob_start();
    include ASSIGNMENT_GENERATOR_PLUGIN_PATH . 'templates/assignment-generator-template.php';
    return ob_get_clean();
}
add_shortcode('assignment_generator', 'assignment_generator_shortcode');

// ================ Enqueue scripts and styles ================
function assignment_generator_scripts() {
    if (!is_singular()) return;

    global $post;

    if (has_shortcode($post->post_content, 'assignment_generator')) {
        wp_enqueue_style(
            'assignment-generator-styles',
            ASSIGNMENT_GENERATOR_PLUGIN_URL . 'assets/css/assignment-generator-styles.css',
            array(),
            '2.2'
        );

        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');

        wp_enqueue_script(
            'assignment-generator-js',
            ASSIGNMENT_GENERATOR_PLUGIN_URL . 'assets/js/assignment-generator.js',
            array('jquery'),
            '2.2',
            true
        );

        wp_localize_script('assignment-generator-js', 'assignmentGenerator', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('assignment_generator_nonce'),
            'blueprint_config' => get_cbse_blueprint_config(),
            'subject_configs' => array(
                'Mathematics' => get_subject_specific_config('Mathematics'),
                'Science' => get_subject_specific_config('Science'),
                'Social Studies' => get_subject_specific_config('Social Studies'),
                'Hindi' => get_subject_specific_config('Hindi'),
                'English' => get_subject_specific_config('English'),
                'Marathi' => get_subject_specific_config('Marathi'),
                'Computer Science' => get_subject_specific_config('Computer Science')
            )
        ));
    }
}
add_action('wp_enqueue_scripts', 'assignment_generator_scripts');

// ================ AJAX Handlers Registration ================
add_action('wp_ajax_generate_assignment', 'handle_generate_assignment');
add_action('wp_ajax_nopriv_generate_assignment', 'handle_generate_assignment');
add_action('wp_ajax_save_assignment', 'save_assignment_to_library');
add_action('wp_ajax_nopriv_save_assignment', 'save_assignment_to_library');
add_action('wp_ajax_get_saved_activities', 'get_saved_activities_callback');
add_action('wp_ajax_nopriv_get_saved_activities', 'get_saved_activities_callback');
add_action('wp_ajax_process_file_with_mistral', 'handle_process_file_with_mistral');
add_action('wp_ajax_nopriv_process_file_with_mistral', 'handle_process_file_with_mistral');
add_action('wp_ajax_save_answer_sheet', 'save_answer_sheet_to_library');
add_action('wp_ajax_nopriv_save_answer_sheet', 'save_answer_sheet_to_library');
add_action('wp_ajax_get_generation_progress', 'get_generation_progress');
add_action('wp_ajax_nopriv_get_generation_progress', 'get_generation_progress');

// ================ Progress Tracking ================
function update_generation_progress($user_id, $progress, $message) {
    set_transient('assignment_progress_' . $user_id, array(
        'progress' => $progress,
        'message' => $message
    ), 300);
}

function get_generation_progress() {
    $user_id = get_current_user_id();
    $progress_data = get_transient('assignment_progress_' . $user_id);
    
    if ($progress_data) {
        wp_send_json_success($progress_data);
    } else {
        wp_send_json_success(array('progress' => 0, 'message' => 'Initializing...'));
    }
}

// ================ Main assignment generation handler ================
function handle_generate_assignment() {
    error_log("=== ASSIGNMENT GENERATION START (v2.2) ===");
    
    // Increase PHP execution time limits
    @set_time_limit(300); // 5 minutes
    @ini_set('max_execution_time', 300);
    
    if (!wp_doing_ajax()) {
        wp_send_json_error('Not an AJAX request');
        return;
    }
    
    $user_id = get_current_user_id();
    
    // Update progress: Starting
    update_generation_progress($user_id, 5, 'Starting generation...');
    
    $grade = sanitize_text_field($_POST['grade']);
    $topic = sanitize_text_field($_POST['topic']);
    $subject = sanitize_text_field($_POST['subject']); 
    $language = sanitize_text_field($_POST['language']);
    $board = isset($_POST['board']) ? sanitize_text_field($_POST['board']) : 'CBSE';
    $paper_duration = isset($_POST['paper_duration']) ? sanitize_text_field($_POST['paper_duration']) : '3 hours';
    $chapter_reference = isset($_POST['chapter_reference']) ? sanitize_text_field($_POST['chapter_reference']) : '';
    $textbook_name = isset($_POST['textbook_name']) ? sanitize_text_field($_POST['textbook_name']) : '';
    
    // Get question counts
    $mcq_count = isset($_POST['mcq_count']) ? intval($_POST['mcq_count']) : 0;
    $fill_in_the_blanks_count = isset($_POST['fill_blanks_count']) ? intval($_POST['fill_blanks_count']) : 0;
    $true_false_count = isset($_POST['true_false_count']) ? intval($_POST['true_false_count']) : 0;
    $very_short_answer_count = isset($_POST['very_short_answer_count']) ? intval($_POST['very_short_answer_count']) : 0;
    $assertion_reason_count = isset($_POST['assertion_reason_count']) ? intval($_POST['assertion_reason_count']) : 0;
    $short_answer_count = isset($_POST['short_answer_count']) ? intval($_POST['short_answer_count']) : 0;
    $numerical_count = isset($_POST['numerical_count']) ? intval($_POST['numerical_count']) : 0;
    $give_reason_count = isset($_POST['give_reason_count']) ? intval($_POST['give_reason_count']) : 0;
    $short_answer_3_count = isset($_POST['short_answer_3_count']) ? intval($_POST['short_answer_3_count']) : 0;
    $numerical_3_count = isset($_POST['numerical_3_count']) ? intval($_POST['numerical_3_count']) : 0;
    $diagram_based_count = isset($_POST['diagram_based_count']) ? intval($_POST['diagram_based_count']) : 0;
    $long_answer_count = isset($_POST['long_answer_count']) ? intval($_POST['long_answer_count']) : 0;
    $case_study_count = isset($_POST['case_study_count']) ? intval($_POST['case_study_count']) : 0;
    $matrix_match_count = isset($_POST['matrix_match_count']) ? intval($_POST['matrix_match_count']) : 0;
    $source_based_count = isset($_POST['source_based_count']) ? intval($_POST['source_based_count']) : 0;
    $map_based_count = isset($_POST['map_based_count']) ? intval($_POST['map_based_count']) : 0;
    
    $saved_item_content = isset($_POST['saved_item_content']) ? $_POST['saved_item_content'] : '';
    $extracted_file_content = isset($_POST['extracted_file_content']) ? $_POST['extracted_file_content'] : '';
    
    // Update progress: Validating
    update_generation_progress($user_id, 10, 'Validating inputs...');
    
    // Build question counts array
    $question_counts = array(
        'mcq' => $mcq_count,
        'fill_blanks' => $fill_in_the_blanks_count,
        'true_false' => $true_false_count,
        'very_short_answer' => $very_short_answer_count,
        'assertion_reason' => $assertion_reason_count,
        'short_answer' => $short_answer_count,
        'numerical' => $numerical_count,
        'give_reason' => $give_reason_count,
        'short_answer_3' => $short_answer_3_count,
        'numerical_3' => $numerical_3_count,
        'diagram_based' => $diagram_based_count,
        'long_answer' => $long_answer_count,
        'case_study' => $case_study_count,
        'matrix_match' => $matrix_match_count,
        'source_based' => $source_based_count,
        'map_based' => $map_based_count
    );
    
    // Calculate total marks
    $blueprint_config = get_cbse_blueprint_config();
    $total_marks = 0;
    foreach ($question_counts as $type => $count) {
        if (isset($blueprint_config['question_types'][$type])) {
            $total_marks += $count * $blueprint_config['question_types'][$type]['marks'];
        }
    }
    
    $total_questions = array_sum($question_counts);
    
    if ($total_questions === 0) {
        wp_send_json_error('Please select at least one question type and specify the number of questions.');
        return;
    }
    
    // Validate maximum questions per type
    foreach ($question_counts as $type => $count) {
        if ($count > 15) {
            wp_send_json_error("Maximum 15 questions allowed for each type. $type has $count.");
            return;
        }
    }

    // Update progress: Building prompt
    update_generation_progress($user_id, 20, 'Building question paper structure...');

    // Build the prompt
    $prompt = build_cbse_blueprint_prompt(
        $topic, $grade, $subject, $language, $board,
        $question_counts, $total_marks, $paper_duration,
        $chapter_reference, $textbook_name,
        $extracted_file_content, $saved_item_content
    );

    // Update progress: Generating
    update_generation_progress($user_id, 30, 'Generating questions with AI...');

    error_log("Sending CBSE Blueprint prompt to OpenAI...");

    // OpenAI API call
    $response = assignment_openai_api_call('gpt-4o-mini', $prompt, $user_id);

    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message());
        return;
    }

    // Update progress: Processing
    update_generation_progress($user_id, 70, 'Processing response...');

    // Process response
    if (is_array($response) && isset($response['sections'])) {
        $questions_data = $response;
        $assignment_title = "$grade - $subject - $topic";
        
        // Update progress: Rendering
        update_generation_progress($user_id, 85, 'Rendering question paper...');
        
        $assignment_html = render_cbse_blueprint_assignment($questions_data, $assignment_title, $total_marks, $paper_duration);
        $answer_sheet_html = generate_cbse_answer_sheet_html($questions_data, $assignment_title);
        
        // Debug logging
        error_log("Answer sheet HTML length: " . strlen($answer_sheet_html));
        error_log("Answer sheet HTML preview: " . substr($answer_sheet_html, 0, 200));
        
        // Update progress: Complete
        update_generation_progress($user_id, 100, 'Complete!');
        
        wp_send_json_success(array(
            'assignment_html' => $assignment_html,
            'answer_sheet_html' => $answer_sheet_html,
            'questions' => $questions_data,
            'assignment_title' => $assignment_title,
            'grade' => $grade,
            'subject' => $subject,
            'language' => $language,
            'total_marks' => $total_marks,
            'paper_duration' => $paper_duration
        ));
    } else {
        // Try to parse text response
        if (is_string($response)) {
            $json_start = strpos($response, '{');
            $json_end = strrpos($response, '}');
            
            if ($json_start !== false && $json_end !== false) {
                $json_content = substr($response, $json_start, $json_end - $json_start + 1);
                $parsed_data = json_decode($json_content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($parsed_data['sections'])) {
                    $questions_data = $parsed_data;
                    $assignment_title = "$grade - $subject - $topic";
                    
                    update_generation_progress($user_id, 85, 'Rendering question paper...');
                    
                    $assignment_html = render_cbse_blueprint_assignment($questions_data, $assignment_title, $total_marks, $paper_duration);
                    $answer_sheet_html = generate_cbse_answer_sheet_html($questions_data, $assignment_title);
                    
                    // Debug logging
                    error_log("Answer sheet HTML length (parsed): " . strlen($answer_sheet_html));
                    
                    update_generation_progress($user_id, 100, 'Complete!');
                    
                    wp_send_json_success(array(
                        'assignment_html' => $assignment_html,
                        'answer_sheet_html' => $answer_sheet_html,
                        'questions' => $questions_data,
                        'assignment_title' => $assignment_title,
                        'grade' => $grade,
                        'subject' => $subject,
                        'language' => $language,
                        'total_marks' => $total_marks,
                        'paper_duration' => $paper_duration
                    ));
                    return;
                }
            }
        }
        
        wp_send_json_error("Failed to generate properly formatted questions. Please try again.");
    }
}

// ================ Build CBSE Blueprint Prompt ================
function build_cbse_blueprint_prompt($topic, $grade, $subject, $language, $board, $question_counts, $total_marks, $paper_duration, $chapter_reference, $textbook_name, $extracted_file_content, $saved_item_content) {
    
    $blueprint_config = get_cbse_blueprint_config();
    $subject_config = get_subject_specific_config($subject);
    
    // Calculate section-wise marks
    $section_a_count = $question_counts['mcq'] + $question_counts['fill_blanks'] + $question_counts['true_false'] + $question_counts['very_short_answer'] + $question_counts['assertion_reason'];
    $section_b_count = $question_counts['short_answer'] + $question_counts['numerical'] + $question_counts['give_reason'];
    $section_c_count = $question_counts['short_answer_3'] + $question_counts['numerical_3'] + $question_counts['diagram_based'];
    $section_d_count = $question_counts['long_answer'] + $question_counts['matrix_match'];
    $section_e_count = $question_counts['case_study'] + $question_counts['source_based'] + $question_counts['map_based'];
    
    $prompt = "You are an expert CBSE question paper generator. Create a properly formatted question paper.

=== PAPER DETAILS ===
Topic/Chapter: '$topic'
Grade/Class: $grade
Subject: $subject
Board: $board
Language: $language
Total Marks: $total_marks
Duration: $paper_duration
";

    if (!empty($chapter_reference)) {
        $prompt .= "Chapter Reference: $chapter_reference\n";
    }
    if (!empty($textbook_name)) {
        $prompt .= "Textbook: $textbook_name\n";
    }

    // Add board-specific instructions
    $prompt .= "\n=== BOARD-SPECIFIC INSTRUCTIONS ===\n";
    switch($board) {
        case 'CBSE':
            $prompt .= "- Follow CBSE curriculum guidelines strictly
- Use NCERT textbook content as primary reference
- Include competency-based questions
- Follow latest CBSE question paper pattern
";
            break;
        case 'ICSE':
            $prompt .= "- Follow ICSE/ISC curriculum standards
- Include analytical and application-based questions
";
            break;
        default:
            $prompt .= "- Follow $board curriculum guidelines\n";
            break;
    }

    // Add subject-specific instructions
    $prompt .= "\n=== SUBJECT-SPECIFIC INSTRUCTIONS ===\n";
    $prompt .= $subject_config['special_instructions'] . "\n";

    // Build section-wise question requirements
    $prompt .= "\n=== SECTION-WISE QUESTION REQUIREMENTS ===\n";
    
    if ($section_a_count > 0) {
        $prompt .= "SECTION A - Objective Type Questions (1 mark each):\n";
        if ($question_counts['mcq'] > 0) $prompt .= "  - MCQ: {$question_counts['mcq']} questions\n";
        if ($question_counts['fill_blanks'] > 0) $prompt .= "  - Fill in the Blanks: {$question_counts['fill_blanks']} questions\n";
        if ($question_counts['true_false'] > 0) $prompt .= "  - True/False: {$question_counts['true_false']} questions\n";
        if ($question_counts['very_short_answer'] > 0) $prompt .= "  - Very Short Answer: {$question_counts['very_short_answer']} questions\n";
        if ($question_counts['assertion_reason'] > 0) $prompt .= "  - Assertion-Reason: {$question_counts['assertion_reason']} questions\n";
        $prompt .= "\n";
    }
    
    if ($section_b_count > 0) {
        $prompt .= "SECTION B - Short Answer Questions I (2 marks each):\n";
        if ($question_counts['short_answer'] > 0) $prompt .= "  - Short Answer: {$question_counts['short_answer']} questions\n";
        if ($question_counts['numerical'] > 0) $prompt .= "  - Numerical: {$question_counts['numerical']} questions\n";
        if ($question_counts['give_reason'] > 0) $prompt .= "  - Give Reason: {$question_counts['give_reason']} questions\n";
        $prompt .= "\n";
    }
    
    if ($section_c_count > 0) {
        $prompt .= "SECTION C - Short Answer Questions II (3 marks each):\n";
        if ($question_counts['short_answer_3'] > 0) $prompt .= "  - Short Answer (3 marks): {$question_counts['short_answer_3']} questions\n";
        if ($question_counts['numerical_3'] > 0) $prompt .= "  - Numerical (3 marks): {$question_counts['numerical_3']} questions\n";
        if ($question_counts['diagram_based'] > 0) $prompt .= "  - Diagram Based: {$question_counts['diagram_based']} questions\n";
        $prompt .= "\n";
    }
    
    if ($section_d_count > 0) {
        $prompt .= "SECTION D - Long Answer Questions (5 marks each):\n";
        if ($question_counts['long_answer'] > 0) $prompt .= "  - Long Answer: {$question_counts['long_answer']} questions\n";
        if ($question_counts['matrix_match'] > 0) $prompt .= "  - Matrix Match: {$question_counts['matrix_match']} questions (4 marks)\n";
        $prompt .= "\n";
    }
    
    if ($section_e_count > 0) {
        $prompt .= "SECTION E - Case Study/Source Based Questions (4 marks each):\n";
        if ($question_counts['case_study'] > 0) $prompt .= "  - Case Study: {$question_counts['case_study']} questions\n";
        if ($question_counts['source_based'] > 0) $prompt .= "  - Source Based: {$question_counts['source_based']} questions\n";
        if ($question_counts['map_based'] > 0) $prompt .= "  - Map Based: {$question_counts['map_based']} questions\n";
        $prompt .= "\n";
    }

    // Add reference content
    if (!empty($extracted_file_content)) {
        $prompt .= "\n=== REFERENCE CONTENT FROM UPLOADED FILES ===\n";
        $prompt .= "Base questions on this content:\n$extracted_file_content\n\n";
    } elseif (!empty($saved_item_content)) {
        $prompt .= "\n=== REFERENCE CONTENT ===\n$saved_item_content\n\n";
    }

    // JSON structure specification
    $prompt .= '
=== REQUIRED JSON OUTPUT FORMAT ===
Respond ONLY with valid JSON in this exact structure:

{
    "paper_info": {
        "title": "' . $subject . ' Question Paper",
        "class": "' . $grade . '",
        "subject": "' . $subject . '",
        "total_marks": ' . $total_marks . ',
        "duration": "' . $paper_duration . '",
        "topic": "' . $topic . '"
    },
    "sections": {
        "A": {
            "name": "Section A - Objective Type Questions",
            "marks_per_question": 1,
            "total_marks": ' . ($section_a_count * 1) . ',
            "questions": []
        },
        "B": {
            "name": "Section B - Short Answer Questions I",
            "marks_per_question": 2,
            "total_marks": ' . ($section_b_count * 2) . ',
            "questions": []
        },
        "C": {
            "name": "Section C - Short Answer Questions II",
            "marks_per_question": 3,
            "total_marks": ' . ($section_c_count * 3) . ',
            "questions": []
        },
        "D": {
            "name": "Section D - Long Answer Questions",
            "marks_per_question": 5,
            "total_marks": ' . ($section_d_count * 5) . ',
            "questions": []
        },
        "E": {
            "name": "Section E - Case Study Questions",
            "marks_per_question": 4,
            "total_marks": ' . ($section_e_count * 4) . ',
            "questions": []
        }
    }
}

=== QUESTION FORMAT EXAMPLES ===

For MCQ:
{"question_number": 1, "question": "Question text?", "type": "mcq", "marks": 1, "options": {"A": "Option 1", "B": "Option 2", "C": "Option 3", "D": "Option 4"}, "correct_answer": "B"}

For Fill in Blanks:
{"question_number": 2, "question": "Fill in the blank: The process of _______ helps plants make food.", "type": "fill_blanks", "marks": 1, "correct_answer": "photosynthesis"}

For True/False:
{"question_number": 3, "question": "Statement here.", "type": "true_false", "marks": 1, "options": {"A": "True", "B": "False"}, "correct_answer": "A"}

For Very Short Answer:
{"question_number": 4, "question": "What is photosynthesis?", "type": "very_short_answer", "marks": 1, "correct_answer": "Brief answer here"}

For Short Answer (2 marks):
{"question_number": 1, "question": "Explain the importance of nutrition.", "type": "short_answer", "marks": 2, "correct_answer": "Detailed answer with 2 key points"}

For Long Answer (5 marks):
{"question_number": 1, "question": "Describe the process in detail.", "type": "long_answer", "marks": 5, "correct_answer": "Comprehensive answer covering 5 key points"}

IMPORTANT FOR CASE STUDY:
For case studies, the "question" field should ONLY contain the passage/scenario. DO NOT include sub-questions in the question field. Sub-questions go ONLY in the "sub_questions" array.

{"question_number": 1, "question": "Read the following passage carefully:\n\n[Write a 150-200 word passage/scenario here. Do NOT include any sub-questions in this field.]", "type": "case_study", "marks": 4, "sub_questions": [{"part": "i", "question": "First sub-question?", "marks": 1, "answer": "Answer 1"}, {"part": "ii", "question": "Second sub-question?", "marks": 1, "answer": "Answer 2"}, {"part": "iii", "question": "Third sub-question?", "marks": 1, "answer": "Answer 3"}, {"part": "iv", "question": "Fourth sub-question?", "marks": 1, "answer": "Answer 4"}], "correct_answer": "See sub-question answers above"}

=== CRITICAL RULES ===
1. Generate in ' . $language . ' language
2. For CASE STUDY: Put ONLY the passage in "question" field. Put sub-questions ONLY in "sub_questions" array. DO NOT duplicate.
3. Include 4 sub-questions for each case study, each worth 1 mark
4. Ensure questions are age-appropriate for ' . $grade . '
5. For numerical questions, include step-by-step solutions in the answer
6. RESPOND WITH ONLY VALID JSON - NO ADDITIONAL TEXT

Generate the complete question paper now:';

    return $prompt;
}

// ================ Render CBSE Blueprint Assignment HTML ================
function render_cbse_blueprint_assignment($questions_data, $assignment_title, $total_marks, $paper_duration) {
    $output = '<div class="cbse-question-paper">';
    
    // Paper Header
    $output .= '<div class="paper-header">';
    if (isset($questions_data['paper_info'])) {
        $info = $questions_data['paper_info'];
        $output .= '<h2 class="paper-title">' . esc_html($info['title'] ?? $assignment_title) . '</h2>';
        $output .= '<div class="paper-info-row">';
        $output .= '<span><strong>Class:</strong> ' . esc_html($info['class'] ?? '') . '</span>';
        $output .= '<span><strong>Subject:</strong> ' . esc_html($info['subject'] ?? '') . '</span>';
        $output .= '<span><strong>Max. Marks:</strong> ' . esc_html($total_marks) . '</span>';
        $output .= '<span><strong>Time:</strong> ' . esc_html($paper_duration) . '</span>';
        $output .= '</div>';
        if (!empty($info['topic'])) {
            $output .= '<div class="paper-topic"><strong>Topic:</strong> ' . esc_html($info['topic']) . '</div>';
        }
    }
    $output .= '</div>';
    
    // Sections
    if (isset($questions_data['sections']) && is_array($questions_data['sections'])) {
        $global_question_number = 1;
        
        foreach ($questions_data['sections'] as $section_key => $section) {
            if (empty($section['questions'])) continue;
            
            $output .= '<div class="section-container">';
            $output .= '<div class="section-header">';
            $output .= '<h3>' . esc_html($section['name'] ?? "Section $section_key") . '</h3>';
            if (isset($section['total_marks']) && $section['total_marks'] > 0) {
                $output .= '<span class="section-marks-badge">' . esc_html($section['total_marks']) . ' Marks</span>';
            }
            $output .= '</div>';
            
            $output .= '<div class="questions-list">';
            foreach ($section['questions'] as $question) {
                $output .= render_single_question($question, $global_question_number);
                $global_question_number++;
            }
            $output .= '</div>';
            $output .= '<div class="section-divider"></div>';
            $output .= '</div>';
        }
    }
    
    $output .= '</div>';
    return $output;
}

// ================ Render Single Question ================
function render_single_question($question, $question_number) {
    $output = '<div class="question-box" data-type="' . esc_attr($question['type'] ?? '') . '">';
    
    // Question header with number and marks
    $output .= '<div class="question-header">';
    $output .= '<span class="question-number">Q' . $question_number . '.</span>';
    $output .= '<span class="question-marks">[' . esc_html($question['marks'] ?? '1') . ']</span>';
    $output .= '</div>';
    
    // Question text - for case study, clean up any embedded sub-questions
    $question_text = $question['question'] ?? '';
    
    // For case study, remove any sub-questions that might be embedded in the question text
    if (($question['type'] === 'case_study' || $question['type'] === 'source_based') && isset($question['sub_questions'])) {
        // Remove common patterns of embedded sub-questions
        $question_text = preg_replace('/\n\s*\([ivxIVX]+\)[^\n]*/s', '', $question_text);
        $question_text = preg_replace('/\n\s*[ivxIVX]+\.[^\n]*/s', '', $question_text);
        $question_text = preg_replace('/\n\s*[a-d]\)[^\n]*/s', '', $question_text);
        $question_text = preg_replace('/\n\s*Q[0-9]+[^\n]*/s', '', $question_text);
        // Clean up multiple newlines
        $question_text = preg_replace('/\n{3,}/', "\n\n", $question_text);
        $question_text = trim($question_text);
    }
    
    $output .= '<div class="question-text">';
    $output .= '<p>' . nl2br(esc_html($question_text)) . '</p>';
    $output .= '</div>';
    
    // Options for MCQ, True/False, Assertion-Reason
    if (isset($question['options']) && is_array($question['options']) && !in_array($question['type'], ['case_study', 'source_based'])) {
        $output .= '<div class="question-options">';
        foreach ($question['options'] as $letter => $option_text) {
            $output .= '<div class="option-item">';
            $output .= '<span class="option-letter">(' . esc_html($letter) . ')</span> ';
            $output .= '<span class="option-text">' . esc_html($option_text) . '</span>';
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    
    // Matrix Match columns
    if ($question['type'] === 'matrix_match' && isset($question['column_a']) && isset($question['column_b'])) {
        $output .= '<div class="matrix-match-container">';
        $output .= '<table class="matrix-table">';
        $output .= '<thead><tr><th>Column A</th><th>Column B</th></tr></thead>';
        $output .= '<tbody>';
        $max_rows = max(count($question['column_a']), count($question['column_b']));
        for ($i = 0; $i < $max_rows; $i++) {
            $output .= '<tr>';
            $output .= '<td>' . ($i + 1) . '. ' . esc_html($question['column_a'][$i] ?? '') . '</td>';
            $output .= '<td>' . chr(65 + $i) . '. ' . esc_html($question['column_b'][$i] ?? '') . '</td>';
            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
        $output .= '</div>';
    }
    
    // Case Study / Source Based sub-questions
    if (($question['type'] === 'case_study' || $question['type'] === 'source_based') && isset($question['sub_questions']) && is_array($question['sub_questions'])) {
        $output .= '<div class="sub-questions">';
        $output .= '<p class="sub-questions-label"><strong>Answer the following questions:</strong></p>';
        foreach ($question['sub_questions'] as $sub) {
            $output .= '<div class="sub-question">';
            $output .= '<span class="sub-part">(' . esc_html($sub['part'] ?? '') . ')</span> ';
            $output .= esc_html($sub['question'] ?? '');
            $output .= ' <span class="sub-marks">[' . esc_html($sub['marks'] ?? 1) . ']</span>';
            $output .= '</div>';
        }
        $output .= '</div>';
    }
    
    // Answer space for subjective questions (but not for case study)
    $subjective_types = array('short_answer', 'numerical', 'give_reason', 'short_answer_3', 'numerical_3', 'diagram_based', 'long_answer', 'very_short_answer', 'fill_blanks');
    if (in_array($question['type'], $subjective_types)) {
        $lines = ($question['marks'] ?? 1) <= 2 ? 3 : (($question['marks'] ?? 1) <= 3 ? 5 : 8);
        $output .= '<div class="answer-space">';
        for ($i = 0; $i < $lines; $i++) {
            $output .= '<div class="answer-line"></div>';
        }
        $output .= '</div>';
    }
    
    // Hidden fields
    $output .= '<input type="hidden" class="question-type" value="' . esc_attr($question['type'] ?? '') . '">';
    $output .= '<input type="hidden" class="correct-answer" value="' . esc_attr($question['correct_answer'] ?? '') . '">';
    
    $output .= '</div>';
    return $output;
}

// ================ Generate CBSE Answer Sheet HTML ================
function generate_cbse_answer_sheet_html($questions_data, $assignment_title) {
    $output = '<div class="answer-sheet cbse-answer-sheet">';
    $output .= '<h2 class="answer-sheet-title">' . esc_html($assignment_title . ' - Answer Key & Marking Scheme') . '</h2>';
    
    if (isset($questions_data['sections']) && is_array($questions_data['sections'])) {
        $global_question_number = 1;
        
        foreach ($questions_data['sections'] as $section_key => $section) {
            if (empty($section['questions'])) continue;
            
            $output .= '<div class="answer-section">';
            $output .= '<h3 class="answer-section-title">' . esc_html($section['name'] ?? "Section $section_key") . '</h3>';
            
            foreach ($section['questions'] as $question) {
                $output .= '<div class="answer-item">';
                
                $output .= '<div class="answer-header">';
                $output .= '<span class="question-num">Q' . $global_question_number . '.</span>';
                $marks = $question['marks'] ?? 1;
                $output .= '<span class="question-marks-badge">[' . esc_html($marks) . ' Mark' . ($marks > 1 ? 's' : '') . ']</span>';
                $output .= '</div>';
                
                // Show question preview
                $preview = substr($question['question'] ?? '', 0, 100);
                if (strlen($question['question'] ?? '') > 100) $preview .= '...';
                $output .= '<div class="question-preview"><em>' . esc_html($preview) . '</em></div>';
                
                // Answer
                $output .= '<div class="answer-content">';
                $output .= '<strong>Answer: </strong>';
                
                if (($question['type'] === 'case_study' || $question['type'] === 'source_based') && isset($question['sub_questions']) && is_array($question['sub_questions'])) {
                    $output .= '<div class="case-study-answers">';
                    foreach ($question['sub_questions'] as $sub) {
                        $output .= '<div class="sub-answer">';
                        $output .= '<span class="sub-part">(' . esc_html($sub['part'] ?? '') . ')</span> ';
                        $output .= '<span class="editable-answer" contenteditable="true">' . esc_html($sub['answer'] ?? 'Answer not provided') . '</span>';
                        $output .= '</div>';
                    }
                    $output .= '</div>';
                } else {
                    $answer = $question['correct_answer'] ?? 'Answer not provided';
                    $output .= '<span class="editable-answer" contenteditable="true">' . esc_html($answer) . '</span>';
                }
                
                $output .= '</div>';
                
                // Marking points for detailed answers (2+ marks)
                if (in_array($question['type'], array('short_answer', 'short_answer_3', 'long_answer', 'numerical', 'numerical_3', 'give_reason', 'diagram_based')) && $marks >= 2) {
                    $output .= '<div class="marking-points">';
                    $output .= '<strong>Marking Scheme:</strong>';
                    $output .= '<ul>';
                    for ($i = 1; $i <= $marks; $i++) {
                        $output .= '<li contenteditable="true">Point ' . $i . ' - (1 mark)</li>';
                    }
                    $output .= '</ul>';
                    $output .= '</div>';
                }
                
                $output .= '</div>';
                $global_question_number++;
            }
            
            $output .= '<div class="section-divider"></div>';
            $output .= '</div>';
        }
    }
    
    $output .= '</div>';
    return $output;
}

// ================ Save Answer Sheet to Library ================
function save_answer_sheet_to_library() {
    global $wpdb;

    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to save an answer sheet.');
        return;
    }

    $user_id = get_current_user_id();
    $answer_sheet_title = sanitize_text_field($_POST['answer_sheet_title']);
    $answer_sheet_content = wp_kses_post($_POST['answer_sheet_content']);
    
    $grade_level = isset($_POST['grade_level']) ? sanitize_text_field($_POST['grade_level']) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
    $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
    $parent_test_id = isset($_POST['parent_test_id']) ? intval($_POST['parent_test_id']) : null;
    
    $metadata = json_encode(array(
        'grade_level' => $grade_level,
        'subject' => $subject,
        'language' => $language,
        'topic' => $topic,
        'parent_test_id' => $parent_test_id
    ));

    $table_name = $wpdb->prefix . 'eduai_tool_data';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_eduai_universal_tool_data_table();
    }

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'tool_type' => 'answer_sheet',
            'title' => $answer_sheet_title,
            'content' => $answer_sheet_content,
            'metadata' => $metadata,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success(array(
            'message' => 'Answer sheet saved successfully.',
            'answer_sheet_id' => $wpdb->insert_id
        ));
    } else {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    }
}

// ================ Save assignment to database ================
function save_assignment_to_library() {
    global $wpdb;

    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to save a test.');
        return;
    }

    $user_id = get_current_user_id();
    $test_title = sanitize_text_field($_POST['test_title']);
    $test_content = wp_kses_post($_POST['test_content']);
    
    $grade_level = isset($_POST['grade_level']) ? sanitize_text_field($_POST['grade_level']) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
    $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
    $board = isset($_POST['board']) ? sanitize_text_field($_POST['board']) : '';
    $total_marks = isset($_POST['total_marks']) ? intval($_POST['total_marks']) : 0;
    
    $metadata = json_encode(array(
        'grade_level' => $grade_level,
        'subject' => $subject,
        'language' => $language,
        'topic' => $topic,
        'board' => $board,
        'total_marks' => $total_marks
    ));

    $table_name = $wpdb->prefix . 'eduai_tool_data';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        create_eduai_universal_tool_data_table();
    }

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'tool_type' => 'test',
            'title' => $test_title,
            'content' => $test_content,
            'metadata' => $metadata,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success(array(
            'message' => 'Test saved successfully.',
            'test_id' => $wpdb->insert_id
        ));
    } else {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    }
}

// ================ Get saved activities from database ================
function get_saved_activities_callback() {
    if (!wp_doing_ajax()) {
        wp_send_json_error('Invalid request type');
        exit;
    }

    if (!is_user_logged_in()) {
        wp_send_json_success([]);
        exit;
    }

    $user_id = get_current_user_id();

    global $wpdb;
    $table_name = $wpdb->prefix . 'eduai_tool_data';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        wp_send_json_success([]);
        exit;
    }

    try {
        $query = $wpdb->prepare(
            "SELECT id, title, content, metadata, tool_type, created_at
            FROM $table_name 
            WHERE user_id = %d
            ORDER BY created_at DESC", 
            $user_id
        );

        $items = $wpdb->get_results($query, ARRAY_A);

        if (empty($items)) {
            wp_send_json_success([]);
            exit;
        }

        $cleaned_items = [];
        foreach ($items as $item) {
            $metadata = json_decode($item['metadata'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $metadata = [];
            }
            
            $cleaned_items[] = [
                'id' => $item['id'],
                'title' => !empty($item['title']) ? sanitize_text_field($item['title']) : 'Untitled',
                'content' => !empty($item['content']) ? wp_kses_post($item['content']) : '',
                'tool_type' => !empty($item['tool_type']) ? sanitize_text_field($item['tool_type']) : 'test',
                'created_at' => $item['created_at'],
                'grade' => isset($metadata['grade_level']) ? sanitize_text_field($metadata['grade_level']) : '',
                'subject' => isset($metadata['subject']) ? sanitize_text_field($metadata['subject']) : '',
                'topic' => isset($metadata['topic']) ? sanitize_text_field($metadata['topic']) : '',
                'language' => isset($metadata['language']) ? sanitize_text_field($metadata['language']) : ''
            ];
        }

        wp_send_json_success($cleaned_items);
        exit;

    } catch (Exception $e) {
        wp_send_json_error('An error occurred while fetching saved items');
        exit;
    }
}

// ================ Handle file processing with Mistral OCR API ================
function handle_process_file_with_mistral() {
    if (!wp_doing_ajax()) {
        wp_send_json_error('Invalid request');
        return;
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('No file uploaded or upload error occurred');
        return;
    }

    $uploaded_file = $_FILES['file'];
    $file_name = sanitize_file_name($uploaded_file['name']);
    $file_type = $uploaded_file['type'];
    $tmp_name = $uploaded_file['tmp_name'];
    $file_index = isset($_POST['file_index']) ? intval($_POST['file_index']) : 0;

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!in_array(strtolower($file_type), $allowed_types)) {
        wp_send_json_error('Unsupported file type. Please upload PDF, JPG, JPEG, or PNG files.');
        return;
    }

    $upload_dir = wp_upload_dir();
    $assignment_upload_dir = $upload_dir['basedir'] . '/assignment-files/';
    
    if (!file_exists($assignment_upload_dir)) {
        wp_mkdir_p($assignment_upload_dir);
    }

    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $unique_filename = 'assignment_' . time() . '_' . $file_index . '_' . wp_generate_password(8, false) . '.' . $file_extension;
    $file_path = $assignment_upload_dir . $unique_filename;

    if (!move_uploaded_file($tmp_name, $file_path)) {
        wp_send_json_error('Failed to save uploaded file');
        return;
    }

    try {
        $extracted_text = extract_text_from_file_mistral_assignment($file_path, $file_type);
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        if (strpos($extracted_text, 'Error:') === 0) {
            wp_send_json_error($extracted_text);
            return;
        }

        wp_send_json_success([
            'extracted_text' => $extracted_text,
            'file_name' => $file_name,
            'file_index' => $file_index
        ]);

    } catch (Exception $e) {
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        wp_send_json_error('An error occurred: ' . $e->getMessage());
    }
}

// ================ Mistral OCR API text extraction function ================
function extract_text_from_file_mistral_assignment($file_path, $file_type = null) {
    $api_key = 'F1dMyB2SjAGmarpPOd1z5dzV0sJfpUvT';
    
    if (!file_exists($file_path)) {
        return 'Error: File does not exist';
    }

    if (empty($file_type)) {
        $file_type = mime_content_type($file_path);
    }

    $file_type = strtolower($file_type);
    if ($file_type === 'image/jpg') {
        $file_type = 'image/jpeg';
    }

    $is_image = in_array($file_type, ['image/jpeg', 'image/jpg', 'image/png']);
    $is_pdf = ($file_type === 'application/pdf');
    
    if (!$is_image && !$is_pdf) {
        return 'Error: Unsupported file type for OCR.';
    }

    $file_data = file_get_contents($file_path);
    if ($file_data === false) {
        return 'Error: Cannot read file';
    }
    
    $base64_data = base64_encode($file_data);
    
    if ($is_image) {
        $data_url = 'data:' . $file_type . ';base64,' . $base64_data;
        $doc_type = 'image_url';
    } else {
        $data_url = 'data:application/pdf;base64,' . $base64_data;
        $doc_type = 'document_url';
    }

    $url = 'https://api.mistral.ai/v1/ocr';
    $payload = json_encode([
        'model' => 'mistral-ocr-latest',
        'document' => [
            'type' => $doc_type,
            $doc_type => $data_url
        ]
    ]);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
        'Accept: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 500
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return "Error: " . curl_error($ch);
    }
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code !== 200) {
        return "Error: API returned status $http_code";
    }
    
    $extracted_text = '';
    
    if (isset($result['pages']) && is_array($result['pages'])) {
        foreach ($result['pages'] as $page) {
            if (isset($page['text'])) {
                $extracted_text .= $page['text'] . "\n\n";
            } elseif (isset($page['markdown'])) {
                $extracted_text .= preg_replace('/!\[.*?\]\(.*?\)/', '', $page['markdown']) . "\n\n";
            }
        }
    }
    
    return trim($extracted_text) ?: "No text could be extracted";
}

// ================ Check membership level ================
function assignpg_check_membership_level() {
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        
        if (function_exists('pmpro_getMembershipLevelForUser')) {
            $membership_level = pmpro_getMembershipLevelForUser($user_id);
        } else {
            $api_key = get_option('openai_api_key');
            return $api_key ? array('level_id' => 1, 'level_name' => 'Default', 'api_key' => $api_key) : false;
        }

        if ($membership_level) {
            $level_id = $membership_level->id;
            $level_name = $membership_level->name;

            $level_3_name = 'EduAI Hub Enterprise';

            if ($level_id == 3 && $level_name == $level_3_name) {
                $personal_api_key = trim(get_user_meta($user_id, 'user_openai_api_key', true));
                if ($personal_api_key) {
                    return array('level_id' => $level_id, 'level_name' => $level_name, 'api_key' => $personal_api_key);
                }
            }
            
            $api_key = trim(get_option('openai_api_key'));
            if ($api_key) {
                return array('level_id' => $level_id, 'level_name' => $level_name, 'api_key' => $api_key);
            }
            return false;
        }
        return new WP_Error('no_membership', 'No membership level found.');
    }
    return new WP_Error('not_logged_in', 'User is not logged in.');
}

// ================ OpenAI API call with progress updates ================
function assignment_openai_api_call($model, $prompt, $user_id = null) {
    $membership_data = assignpg_check_membership_level();
    if (is_wp_error($membership_data)) {
        return $membership_data;
    }

    $api_key = $membership_data['api_key'];
    if (!$api_key) {
        return new WP_Error('no_api_key', 'OpenAI API key is missing.');
    }

    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // Check if Assistant API is enabled
    if (function_exists('eduai_is_assistant_enabled') && eduai_is_assistant_enabled()) {
        update_generation_progress($user_id, 40, 'Connecting to AI assistant...');
        
        $api_options = array(
            'model' => $model,
            'max_tokens' => 8000,
            'temperature' => 0.3,
            'source_plugin' => 'Assignment Generator v2.2'
        );
        
        $api_response = eduai_call_ai($prompt, $api_options, $user_id);
        
        update_generation_progress($user_id, 60, 'Receiving response...');
        
        if (!$api_response['success']) {
            return new WP_Error('api_error', $api_response['error']);
        }
        
        $content = trim($api_response['content']);
        $json_decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_decoded;
        }
        return $content;
    }

    // Direct OpenAI API
    update_generation_progress($user_id, 40, 'Connecting to OpenAI...');
    
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = array(
        'model' => $model,
        'messages' => array(
            array('role' => 'system', 'content' => 'You are a CBSE question paper generator. Always respond with valid JSON only. For case study questions, put the passage in "question" field and sub-questions ONLY in "sub_questions" array - never duplicate them.'),
            array('role' => 'user', 'content' => $prompt)
        ),
        'max_tokens' => 8000,
        'temperature' => 0.3,
    );

    update_generation_progress($user_id, 50, 'Generating questions...');

    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($data),
        'timeout' => 180,
        'sslverify' => false,
    ));

    update_generation_progress($user_id, 60, 'Receiving response...');

    if (is_wp_error($response)) {
        return $response;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code === 200 && isset($body['choices'][0]['message']['content'])) {
        $content = trim($body['choices'][0]['message']['content']);
        $json_decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_decoded;
        }
        return $content;
    }
    
    return new WP_Error('invalid_response', 'Invalid API response.');
}