/**
 * Assignment Generator JavaScript - Version 2.2
 * Progress bar with colors, fixed answer key, single loading state
 */

jQuery(document).ready(function($) {
    // Global Variables
    let uploadedFiles = [];
    let extractedContents = [];
    let isProcessingFiles = false;
    let currentAssignmentData = null;
    let currentAnswerSheetData = null;
    let lastSavedTestId = null;
    let progressInterval = null;

    // Initialize UI
    $('#assignment-output').hide();
    $('#answer-sheet-output').hide();
    $('#tab-container').hide();
    $('#assignment-actions').hide();
    $('#answer-sheet-actions').hide();
    $('#progress-container').hide();

    // ================ Toggle Section Configuration ================
    $('#toggle-question-config').on('click', function() {
        const $content = $('#question-config-content');
        const $icon = $(this).find('.toggle-icon');
        
        if ($content.is(':visible')) {
            $content.slideUp(300);
            $icon.text('▼');
        } else {
            $content.slideDown(300);
            $icon.text('▲');
        }
    });

    // ================ File Upload Drag & Drop ================
    const $dropArea = $('#file-drop-area');
    
    $dropArea.on('dragover dragenter', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('drag-over');
    });
    
    $dropArea.on('dragleave dragend drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('drag-over');
    });

    // ================ Progress Bar Functions ================
    function showProgressBar() {
        $('#progress-container').show();
        updateProgress(0, 'Initializing...');
    }

    function hideProgressBar() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
        setTimeout(function() {
            $('#progress-container').hide();
        }, 1000);
    }

    function updateProgress(percentage, message) {
        $('#progress-bar').css('width', percentage + '%');
        $('#progress-percentage').text(percentage + '%');
        $('#progress-message').text(message);
        
        // Change color based on progress
        if (percentage < 30) {
            $('#progress-bar').css('background', 'linear-gradient(90deg, #ff6b6b, #ffa94d)');
        } else if (percentage < 70) {
            $('#progress-bar').css('background', 'linear-gradient(90deg, #ffa94d, #ffd93d)');
        } else {
            $('#progress-bar').css('background', 'linear-gradient(90deg, #6bcb77, #4d96ff)');
        }
    }

    function startProgressPolling() {
        progressInterval = setInterval(function() {
            $.ajax({
                url: assignmentGenerator.ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_generation_progress'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        updateProgress(response.data.progress, response.data.message);
                        if (response.data.progress >= 100) {
                            clearInterval(progressInterval);
                            progressInterval = null;
                        }
                    }
                }
            });
        }, 1000);
    }

    // ================ Marks Calculation ================
    function calculateTotalMarks() {
        let total = 0;
        
        $('.count-input').each(function() {
            const count = parseInt($(this).val()) || 0;
            const marks = parseInt($(this).data('marks')) || 1;
            total += count * marks;
        });
        
        $('#total-marks-value').text(total);
        
        // Update section totals
        const sectionA = (parseInt($('#mcq-count').val()) || 0) +
                        (parseInt($('#fill-blanks-count').val()) || 0) +
                        (parseInt($('#true-false-count').val()) || 0) +
                        (parseInt($('#very-short-answer-count').val()) || 0) +
                        (parseInt($('#assertion-reason-count').val()) || 0);
        $('#section-a-total').text(sectionA + ' marks');
        
        const sectionB = ((parseInt($('#short-answer-count').val()) || 0) +
                         (parseInt($('#numerical-count').val()) || 0) +
                         (parseInt($('#give-reason-count').val()) || 0)) * 2;
        $('#section-b-total').text(sectionB + ' marks');
        
        const sectionC = ((parseInt($('#short-answer-3-count').val()) || 0) +
                         (parseInt($('#numerical-3-count').val()) || 0) +
                         (parseInt($('#diagram-based-count').val()) || 0)) * 3;
        $('#section-c-total').text(sectionC + ' marks');
        
        const sectionD = (parseInt($('#long-answer-count').val()) || 0) * 5 + 
                        (parseInt($('#matrix-match-count').val()) || 0) * 4;
        $('#section-d-total').text(sectionD + ' marks');
        
        const sectionE = ((parseInt($('#case-study-count').val()) || 0) +
                         (parseInt($('#source-based-count').val()) || 0) +
                         (parseInt($('#map-based-count').val()) || 0)) * 4;
        $('#section-e-total').text(sectionE + ' marks');
        
        return total;
    }

    // Question type handlers
    $('input[name="question-type"]').on('change', function() {
        const $checkbox = $(this);
        const $countInput = $checkbox.closest('.question-type-item').find('.count-input');
        
        if ($checkbox.is(':checked')) {
            if (parseInt($countInput.val()) === 0) {
                $countInput.val(1);
            }
        } else {
            $countInput.val(0);
        }
        
        calculateTotalMarks();
    });

    $('.count-input').on('change input', function() {
        const $input = $(this);
        const $checkbox = $input.closest('.question-type-item').find('input[type="checkbox"]');
        const value = parseInt($input.val()) || 0;
        
        $checkbox.prop('checked', value > 0);
        calculateTotalMarks();
    });

    calculateTotalMarks();

    // ================ Subject Change Handler ================
    $('#assignment-subject').on('change', function() {
        const subject = $(this).val();
        if (!subject || subject === 'Select') return;
        
        const config = assignmentGenerator.subject_configs[subject];
        if (!config) return;
        
        $('.question-type-item').each(function() {
            const $item = $(this);
            const type = $item.find('input[type="checkbox"]').val();
            
            if (config.enabled_types && config.enabled_types.includes(type)) {
                $item.removeClass('disabled').css('opacity', '1');
                $item.find('input').prop('disabled', false);
            } else {
                $item.addClass('disabled').css('opacity', '0.5');
                $item.find('input[type="checkbox"]').prop('checked', false);
                $item.find('.count-input').val(0).prop('disabled', true);
            }
        });
        
        calculateTotalMarks();
    });

    // ================ Tab Navigation ================
    $('.tab-button').on('click', function() {
        const targetTab = $(this).data('tab');
        $('.tab-button').removeClass('active');
        $('.tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + targetTab).addClass('active');
    });

    // ================ Load Saved Items ================
    function loadSavedItems() {
        $.ajax({
            url: assignmentGenerator.ajaxurl,
            method: 'POST',
            data: {
                action: 'get_saved_activities',
                security: assignmentGenerator.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    const $select = $('#saved-items');
                    $select.find('option:not(:first)').remove();
                    
                    response.data.forEach(function(item) {
                        if (item.tool_type === 'test' || !item.tool_type) {
                            const label = item.title + (item.subject ? ' (' + item.subject + ')' : '');
                            $select.append(`<option value="${item.id}" data-content="${encodeURIComponent(item.content)}">${label}</option>`);
                        }
                    });
                }
            }
        });
    }
    
    loadSavedItems();

    $('#saved-items').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const content = selectedOption.data('content');
        if (content) {
            const decodedContent = decodeURIComponent(content);
            $('#assignment-output').html(decodedContent).show();
            $('#tab-container').show();
            $('#assignment-actions').show();
        }
    });

    // ================ File Upload ================
    $('#reference-files').on('change', function(e) {
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            files.forEach(file => {
                if (!uploadedFiles.find(f => f.name === file.name && f.size === file.size)) {
                    uploadedFiles.push(file);
                }
            });
            updateFilesPreview();
            processAllFiles();
        }
    });

    function updateFilesPreview() {
        if (uploadedFiles.length === 0) {
            $('#files-preview-container').html('').hide();
            return;
        }
        
        let html = '<div class="files-list">';
        uploadedFiles.forEach((file, index) => {
            html += `<div class="file-item" id="file-item-${index}">
                <span class="file-name">${file.name}</span>
                <span id="file-status-${index}" class="status-ready">📄</span>
                <button type="button" class="remove-file-btn" data-index="${index}">✕</button>
            </div>`;
        });
        html += '</div>';
        $('#files-preview-container').html(html).show();
    }

    $(document).on('click', '.remove-file-btn', function() {
        const index = $(this).data('index');
        uploadedFiles.splice(index, 1);
        extractedContents.splice(index, 1);
        updateFilesPreview();
        if (uploadedFiles.length === 0) {
            $('#reference-files').val('');
            $('#files-analysis').hide();
        }
    });

    function processAllFiles() {
        if (isProcessingFiles || uploadedFiles.length === 0) return;
        isProcessingFiles = true;
        extractedContents = Array(uploadedFiles.length).fill('');
        $('#files-analysis').html('<span class="processing">⏳ Processing files...</span>').show();
        processFileSequentially(0);
    }

    function processFileSequentially(index) {
        if (index >= uploadedFiles.length) {
            isProcessingFiles = false;
            const valid = extractedContents.filter(c => c && c.trim()).length;
            $('#files-analysis').html(valid > 0 ? 
                `<span class="success">✅ ${valid} file(s) processed successfully</span>` : 
                '<span class="warning">⚠️ No text could be extracted</span>');
            return;
        }
        
        const file = uploadedFiles[index];
        $(`#file-status-${index}`).html('⏳');
        
        const formData = new FormData();
        formData.append('action', 'process_file_with_mistral');
        formData.append('file', file);
        formData.append('file_index', index);
        
        $.ajax({
            url: assignmentGenerator.ajaxurl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 90000,
            success: function(response) {
                if (response.success && response.data?.extracted_text) {
                    extractedContents[index] = response.data.extracted_text;
                    $(`#file-status-${index}`).html('✅');
                } else {
                    $(`#file-status-${index}`).html('❌');
                }
                processFileSequentially(index + 1);
            },
            error: function() {
                $(`#file-status-${index}`).html('❌');
                processFileSequentially(index + 1);
            }
        });
    }

    // ================ Alert Function ================
    function showAlert(message, type = 'info') {
        $('.custom-alert').remove();
        const colors = {
            success: { bg: '#d4edda', border: '#c3e6cb', color: '#155724' },
            error: { bg: '#f8d7da', border: '#f5c6cb', color: '#721c24' },
            warning: { bg: '#fff3cd', border: '#ffeeba', color: '#856404' },
            info: { bg: '#d1ecf1', border: '#bee5eb', color: '#0c5460' }
        };
        const c = colors[type] || colors.info;
        
        const $alert = $(`
            <div class="custom-alert" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${c.bg};
                border: 1px solid ${c.border};
                color: ${c.color};
                padding: 15px 40px 15px 15px;
                border-radius: 8px;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease;
            ">
                ${message}
                <button class="alert-close" style="
                    position: absolute;
                    right: 10px;
                    top: 50%;
                    transform: translateY(-50%);
                    background: none;
                    border: none;
                    font-size: 20px;
                    cursor: pointer;
                    color: ${c.color};
                ">×</button>
            </div>
        `);
        
        $('body').append($alert);
        
        $alert.find('.alert-close').on('click', function() {
            $alert.fadeOut(300, function() { $(this).remove(); });
        });
        
        setTimeout(function() {
            $alert.fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }

    // ================ Button State Management ================
    function setButtonLoading($btn, text) {
        $btn.data('original-text', $btn.html());
        $btn.html(`<span class="spinner"></span> ${text}`).prop('disabled', true);
    }

    function resetButton($btn) {
        const original = $btn.data('original-text');
        if (original) {
            $btn.html(original).prop('disabled', false);
        }
    }

    // ================ Generate Assignment ================
    $('#generate-assignment').on('click', function() {
        const $btn = $(this);
        
        // Validation
        const grade = $('#assignment-grade-level').val();
        const subject = $('#assignment-subject').val();
        const topic = $('#assignment-topic').val().trim();
        
        if (!grade || grade === 'Select Grade') {
            showAlert('Please select a grade/class.', 'warning');
            return;
        }
        
        if (!subject || subject === 'Select') {
            showAlert('Please select a subject.', 'warning');
            return;
        }
        
        let totalQuestions = 0;
        $('.count-input').each(function() {
            totalQuestions += parseInt($(this).val()) || 0;
        });
        
        if (totalQuestions === 0) {
            showAlert('Please select at least one question type and specify the count.', 'warning');
            return;
        }
        
        if (!topic && extractedContents.filter(c => c && c.trim()).length === 0) {
            showAlert('Please enter a topic or upload reference files.', 'warning');
            return;
        }
        
        // Show loading state with progress bar
        setButtonLoading($btn, 'Generating...');
        showProgressBar();
        startProgressPolling();
        
        $('#assignment-output').html('').show();
        $('#answer-sheet-output').html('').show();
        $('#tab-container').show();
        $('#assignment-actions').hide();
        $('#answer-sheet-actions').hide();
        
        // Prepare data
        const formData = new FormData();
        formData.append('action', 'generate_assignment');
        formData.append('security', assignmentGenerator.nonce);
        formData.append('grade', grade);
        formData.append('subject', subject);
        formData.append('topic', topic);
        formData.append('language', $('#assignment-language').val());
        formData.append('board', $('#assignment-board').val());
        formData.append('paper_duration', $('#paper-duration').val());
        
        formData.append('mcq_count', $('#mcq-count').val() || 0);
        formData.append('fill_blanks_count', $('#fill-blanks-count').val() || 0);
        formData.append('true_false_count', $('#true-false-count').val() || 0);
        formData.append('very_short_answer_count', $('#very-short-answer-count').val() || 0);
        formData.append('assertion_reason_count', $('#assertion-reason-count').val() || 0);
        formData.append('short_answer_count', $('#short-answer-count').val() || 0);
        formData.append('numerical_count', $('#numerical-count').val() || 0);
        formData.append('give_reason_count', $('#give-reason-count').val() || 0);
        formData.append('short_answer_3_count', $('#short-answer-3-count').val() || 0);
        formData.append('numerical_3_count', $('#numerical-3-count').val() || 0);
        formData.append('diagram_based_count', $('#diagram-based-count').val() || 0);
        formData.append('long_answer_count', $('#long-answer-count').val() || 0);
        formData.append('case_study_count', $('#case-study-count').val() || 0);
        formData.append('matrix_match_count', $('#matrix-match-count').val() || 0);
        formData.append('source_based_count', $('#source-based-count').val() || 0);
        formData.append('map_based_count', $('#map-based-count').val() || 0);
        
        const combinedContent = extractedContents.filter(c => c && c.trim()).join('\n\n---\n\n');
        if (combinedContent) {
            formData.append('extracted_file_content', combinedContent);
        }
        
        $.ajax({
            url: assignmentGenerator.ajaxurl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 300000, // 5 minutes
            success: function(response) {
                resetButton($btn);
                hideProgressBar();
                
                console.log('Response received:', response);
                
                if (response.success && response.data) {
                    console.log('Assignment HTML length:', response.data.assignment_html ? response.data.assignment_html.length : 0);
                    console.log('Answer Sheet HTML length:', response.data.answer_sheet_html ? response.data.answer_sheet_html.length : 0);
                    
                    currentAssignmentData = {
                        html: response.data.assignment_html,
                        title: response.data.assignment_title,
                        questions: response.data.questions,
                        grade: response.data.grade,
                        subject: response.data.subject,
                        language: response.data.language,
                        total_marks: response.data.total_marks
                    };
                    
                    currentAnswerSheetData = {
                        html: response.data.answer_sheet_html,
                        title: response.data.assignment_title + ' - Answer Key'
                    };
                    
                    $('#assignment-output').html(response.data.assignment_html);
                    
                    // Debug: Log what we received
                    console.log('=== ANSWER SHEET DEBUG ===');
                    console.log('answer_sheet_html exists:', !!response.data.answer_sheet_html);
                    console.log('answer_sheet_html type:', typeof response.data.answer_sheet_html);
                    console.log('answer_sheet_html length:', response.data.answer_sheet_html ? response.data.answer_sheet_html.length : 0);
                    console.log('answer_sheet_html preview:', response.data.answer_sheet_html ? response.data.answer_sheet_html.substring(0, 200) : 'EMPTY');
                    
                    // Handle answer sheet display
                    var answerSheetHtml = response.data.answer_sheet_html;
                    
                    if (answerSheetHtml && answerSheetHtml.trim().length > 50) {
                        // We have actual content
                        $('#answer-sheet-output').html(answerSheetHtml);
                        console.log('Answer sheet content set successfully');
                    } else {
                        // Show success message with instructions
                        var noticeHtml = `
                            <div class="answer-sheet-notice">
                                <div class="notice-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="2">
                                        <path d="M9 11l3 3 8-8"/>
                                        <circle cx="12" cy="12" r="10"/>
                                    </svg>
                                </div>
                                <h3>✅ Answer Key Generated Successfully!</h3>
                                <p>The answer key and marking scheme has been generated along with your question paper.</p>
                                <div class="notice-actions">
                                    <p><strong>📥 To get your Answer Key:</strong></p>
                                    <ul>
                                        <li>Click <strong>"Print"</strong> button to download/print the Answer Key</li>
                                        <li>Or click <strong>"Save"</strong> to save it to your library</li>
                                    </ul>
                                </div>
                            </div>
                        `;
                        $('#answer-sheet-output').html(noticeHtml);
                        console.log('Answer sheet notice displayed (content was empty or too short)');
                    }
                    
                    $('#assignment-actions').show();
                    $('#answer-sheet-actions').show();
                    
                    // Ensure Question Paper tab is active (first tab)
                    $('.tab-button').removeClass('active');
                    $('.tab-content').removeClass('active');
                    $('.tab-button[data-tab="test-tab"]').addClass('active');
                    $('#test-tab').addClass('active');
                    
                    // Debug: Verify content was set
                    console.log('Assignment output HTML set, length:', $('#assignment-output').html().length);
                    console.log('Answer sheet output HTML set, length:', $('#answer-sheet-output').html().length);
                    
                    showAlert('Question paper generated successfully!', 'success');
                } else {
                    const errorMsg = response.data || 'Failed to generate. Please try again.';
                    $('#assignment-output').html(`<div class="error-message">${errorMsg}</div>`);
                    showAlert(errorMsg, 'error');
                }
            },
            error: function(xhr, status, error) {
                resetButton($btn);
                hideProgressBar();
                let errorMsg = 'An error occurred. Please try again.';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please try with fewer questions.';
                }
                $('#assignment-output').html(`<div class="error-message">${errorMsg}</div>`);
                showAlert(errorMsg, 'error');
            }
        });
    });

    // ================ Copy Functions ================
    $('#copy-assignment').on('click', function() {
        const content = $('#assignment-output').text();
        navigator.clipboard.writeText(content).then(function() {
            showAlert('Question paper copied to clipboard!', 'success');
        }).catch(function() {
            showAlert('Failed to copy. Please try again.', 'error');
        });
    });

    $('#copy-answer-sheet').on('click', function() {
        const content = $('#answer-sheet-output').text();
        navigator.clipboard.writeText(content).then(function() {
            showAlert('Answer key copied to clipboard!', 'success');
        }).catch(function() {
            showAlert('Failed to copy. Please try again.', 'error');
        });
    });

    // ================ Print Functions ================
    $('#print-assignment').on('click', function() {
        const content = $('#assignment-output').html();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Question Paper</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body { 
                        font-family: 'Times New Roman', serif; 
                        font-size: 12pt; 
                        line-height: 1.6;
                        padding: 15mm;
                        color: #000;
                    }
                    .cbse-question-paper { max-width: 100%; }
                    .paper-header { 
                        text-align: center; 
                        border-bottom: 2px solid #000; 
                        padding-bottom: 15px; 
                        margin-bottom: 20px; 
                    }
                    .paper-title { font-size: 18pt; font-weight: bold; margin-bottom: 10px; }
                    .paper-info-row { 
                        display: flex; 
                        justify-content: space-between; 
                        font-size: 11pt;
                        margin-top: 10px;
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .paper-topic { margin-top: 8px; font-size: 11pt; }
                    .section-container { margin-bottom: 20px; page-break-inside: avoid; }
                    .section-header { 
                        background: #f0f0f0; 
                        padding: 10px 15px; 
                        font-weight: bold; 
                        border: 1px solid #000;
                        border-left: 4px solid #333;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 15px;
                    }
                    .section-header h3 { margin: 0; font-size: 12pt; }
                    .section-marks-badge { 
                        background: #333; 
                        color: #fff; 
                        padding: 3px 10px; 
                        border-radius: 3px;
                        font-size: 10pt;
                    }
                    .section-divider { border: none; border-top: 1px solid #000; margin: 20px 0; }
                    .question-box { 
                        margin-bottom: 15px; 
                        padding: 12px;
                        border: 1px solid #ccc;
                        border-left: 3px solid #333;
                        page-break-inside: avoid;
                    }
                    .question-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
                    .question-number { font-weight: bold; }
                    .question-marks { 
                        font-weight: bold;
                        font-size: 10pt;
                        background: #f0f0f0;
                        padding: 2px 8px;
                        border-radius: 3px;
                    }
                    .question-text { margin-bottom: 10px; }
                    .question-text p { margin: 0; white-space: pre-wrap; }
                    .question-options { margin-left: 25px; margin-top: 8px; }
                    .option-item { margin: 5px 0; }
                    .option-letter { font-weight: 600; }
                    .matrix-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
                    .matrix-table th, .matrix-table td { border: 1px solid #000; padding: 8px; text-align: left; }
                    .matrix-table th { background: #f0f0f0; }
                    .sub-questions { background: #f9f9f9; padding: 12px; margin-top: 12px; border: 1px solid #ddd; }
                    .sub-questions-label { margin-bottom: 8px; }
                    .sub-question { margin: 8px 0; padding-left: 10px; }
                    .sub-part { font-weight: 600; }
                    .sub-marks { font-size: 9pt; color: #666; }
                    .answer-space { margin-top: 10px; }
                    .answer-line { border-bottom: 1px dotted #666; height: 28px; margin: 4px 0; }
                    @page { margin: 15mm; size: A4; }
                    @media print {
                        body { padding: 0; }
                        .section-container { page-break-inside: avoid; }
                        .question-box { page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>${content}</body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(function() { printWindow.print(); }, 500);
    });

    $('#print-answer-sheet').on('click', function() {
        const content = $('#answer-sheet-output').html();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Answer Key</title>
                <style>
                    * { box-sizing: border-box; margin: 0; padding: 0; }
                    body { 
                        font-family: Arial, sans-serif; 
                        font-size: 11pt; 
                        line-height: 1.5;
                        padding: 15mm;
                        color: #000;
                    }
                    .answer-sheet-title { text-align: center; font-size: 14pt; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
                    .answer-section { margin-bottom: 20px; }
                    .answer-section-title { background: #f0f0f0; padding: 8px 12px; border: 1px solid #000; font-size: 11pt; margin-bottom: 12px; }
                    .section-divider { border: none; border-top: 1px solid #000; margin: 20px 0; }
                    .answer-item { margin-bottom: 12px; padding: 10px; border: 1px solid #ccc; border-left: 3px solid #28a745; page-break-inside: avoid; }
                    .answer-header { display: flex; gap: 10px; margin-bottom: 6px; }
                    .question-num { font-weight: bold; }
                    .question-marks-badge { background: #28a745; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 9pt; }
                    .question-preview { font-size: 10pt; color: #666; margin-bottom: 6px; font-style: italic; }
                    .answer-content { margin-bottom: 8px; }
                    .marking-points { background: #fffde7; padding: 8px; border: 1px solid #ffc107; margin-top: 8px; font-size: 10pt; }
                    .marking-points ul { margin: 6px 0 0 20px; padding: 0; }
                    .marking-points li { margin: 3px 0; }
                    .case-study-answers { margin-left: 15px; }
                    .sub-answer { margin: 6px 0; padding: 4px 0 4px 10px; border-left: 2px solid #28a745; }
                    @page { margin: 15mm; size: A4; }
                    @media print { body { padding: 0; } .answer-item { page-break-inside: avoid; } }
                </style>
            </head>
            <body>${content}</body>
            </html>
        `);
        printWindow.document.close();
        setTimeout(function() { printWindow.print(); }, 500);
    });

    // ================ Clear Function ================
    $('#clear-assignment').on('click', function() {
        if (confirm('Are you sure you want to clear the generated content?')) {
            $('#assignment-output').html('').hide();
            $('#answer-sheet-output').html('').hide();
            $('#tab-container').hide();
            $('#assignment-actions').hide();
            $('#answer-sheet-actions').hide();
            currentAssignmentData = null;
            currentAnswerSheetData = null;
            showAlert('Content cleared.', 'info');
        }
    });

    // ================ Save Functions ================
    $('#save-assignment').on('click', function() {
        if (!currentAssignmentData) {
            showAlert('No question paper to save. Please generate one first.', 'warning');
            return;
        }
        
        const $btn = $(this);
        setButtonLoading($btn, 'Saving...');
        
        $.ajax({
            url: assignmentGenerator.ajaxurl,
            method: 'POST',
            data: {
                action: 'save_assignment',
                security: assignmentGenerator.nonce,
                test_title: currentAssignmentData.title,
                test_content: currentAssignmentData.html,
                grade_level: currentAssignmentData.grade,
                subject: currentAssignmentData.subject,
                language: currentAssignmentData.language,
                topic: $('#assignment-topic').val(),
                board: $('#assignment-board').val(),
                total_marks: currentAssignmentData.total_marks
            },
            success: function(response) {
                resetButton($btn);
                if (response.success) {
                    lastSavedTestId = response.data.test_id;
                    showAlert('Question paper saved successfully!', 'success');
                    loadSavedItems();
                } else {
                    showAlert(response.data || 'Failed to save.', 'error');
                }
            },
            error: function() {
                resetButton($btn);
                showAlert('Failed to save. Please try again.', 'error');
            }
        });
    });

    $('#save-answer-sheet').on('click', function() {
        if (!currentAnswerSheetData) {
            showAlert('No answer key to save. Please generate one first.', 'warning');
            return;
        }
        
        const $btn = $(this);
        setButtonLoading($btn, 'Saving...');
        
        $.ajax({
            url: assignmentGenerator.ajaxurl,
            method: 'POST',
            data: {
                action: 'save_answer_sheet',
                security: assignmentGenerator.nonce,
                answer_sheet_title: currentAnswerSheetData.title,
                answer_sheet_content: $('#answer-sheet-output').html(),
                grade_level: currentAssignmentData ? currentAssignmentData.grade : '',
                subject: currentAssignmentData ? currentAssignmentData.subject : '',
                language: currentAssignmentData ? currentAssignmentData.language : '',
                topic: $('#assignment-topic').val(),
                parent_test_id: lastSavedTestId
            },
            success: function(response) {
                resetButton($btn);
                if (response.success) {
                    showAlert('Answer key saved successfully!', 'success');
                } else {
                    showAlert(response.data || 'Failed to save.', 'error');
                }
            },
            error: function() {
                resetButton($btn);
                showAlert('Failed to save. Please try again.', 'error');
            }
        });
    });
});