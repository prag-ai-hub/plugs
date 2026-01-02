<?php
/**
 * Assignment Generator Template - CBSE Blueprint Version 2.2.1
 * Fixed form layout, answer sheet display, wider container
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<a href="https://vidyahub.eduaihub.in/tools/" id="back-to-tools">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    Back to Tools
</a>

<div id="assignment-generator">
    <h2>📝 Question Paper Generator</h2>
    
    <!-- Paper Configuration Section -->
    <div class="config-section">
        <h3>📋 Paper Configuration</h3>
        
        <!-- Row 1: Saved Items (full width) -->
        <div class="form-row-full">
            <div class="form-field">
                <label for="saved-items">📁 Saved Items:</label>
                <select id="saved-items" name="saved-items">
                    <option value="">Choose from Saved Items</option>
                </select>
            </div>
        </div>
        
        <!-- Row 2: Class, Subject, Duration (3 columns) -->
        <div class="form-row-3">
            <div class="form-field">
                <label for="assignment-grade-level">🎓 Class/Grade:</label>
                <select id="assignment-grade-level" name="assignment-grade-level">
                    <option value="Select Grade">Select Grade</option>
                    <option value="Class 1">Class 1</option>
                    <option value="Class 2">Class 2</option>
                    <option value="Class 3">Class 3</option>
                    <option value="Class 4">Class 4</option>
                    <option value="Class 5">Class 5</option>
                    <option value="Class 6">Class 6</option>
                    <option value="Class 7">Class 7</option>
                    <option value="Class 8">Class 8</option>
                    <option value="Class 9">Class 9</option>
                    <option value="Class 10">Class 10</option>
                    <option value="Class 11">Class 11</option>
                    <option value="Class 12">Class 12</option>
                </select>
            </div>

            <div class="form-field">
                <label for="assignment-subject">📚 Subject:</label>
                <select id="assignment-subject" name="assignment-subject">
                    <option value="Select">Select Subject</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Science">Science</option>
                    <option value="Physics">Physics</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Biology">Biology</option>
                    <option value="Social Studies">Social Studies</option>
                    <option value="History">History</option>
                    <option value="Geography">Geography</option>
                    <option value="Civics">Civics</option>
                    <option value="Economics">Economics</option>
                    <option value="English">English</option>
                    <option value="Hindi">Hindi</option>
                    <option value="Marathi">Marathi</option>
                    <option value="Sanskrit">Sanskrit</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Accountancy">Accountancy</option>
                    <option value="Business Studies">Business Studies</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-field">
                <label for="paper-duration">⏱️ Duration:</label>
                <select id="paper-duration" name="paper-duration">
                    <option value="1 hour">1 Hour</option>
                    <option value="1.5 hours">1.5 Hours</option>
                    <option value="2 hours">2 Hours</option>
                    <option value="3 hours" selected>3 Hours</option>
                </select>
            </div>
        </div>

        <!-- Row 3: Board and Language (2 columns) -->
        <div class="form-row-2">
            <div class="form-field">
                <label for="assignment-board">🏫 Board:</label>
                <select id="assignment-board" name="assignment-board">
                    <option value="CBSE" selected>CBSE</option>
                    <option value="ICSE">ICSE</option>
                    <option value="ISC">ISC</option>
                    <option value="State Board">State Board</option>
                    <option value="Maharashtra State Board">Maharashtra State Board</option>
                    <option value="NIOS">NIOS</option>
                    <option value="IB">IB</option>
                    <option value="Cambridge">Cambridge</option>
                    <option value="Other">Other</option>
                </select>
            </div>
             
            <div class="form-field">
                <label for="assignment-language">🌐 Language:</label>
                <select id="assignment-language" name="assignment-language">
                    <option value="English" selected>English</option>           
                    <option value="Hindi">Hindi</option>
                    <option value="Marathi">Marathi</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Topic & Reference Section -->
    <div class="config-section">
        <h3>📖 Topic & Reference</h3>
        
        <div class="form-field full-width">
            <label for="assignment-topic">Topic/Chapter Name:</label>
            <textarea id="assignment-topic" name="assignment-topic" placeholder="Enter chapter name or topic (e.g., 'Light - Reflection and Refraction')"></textarea>
        </div>

        <div class="file-upload-container">
            <label for="reference-files">📄 Upload Reference Materials (Optional):</label>
            <div class="file-upload-area" id="file-drop-area">
                <div class="file-upload-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#0073aa" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <p class="file-upload-text">Drag & drop files here</p>
                <p class="file-or-text">— or —</p>
                <label for="reference-files" class="file-choose-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Choose Files
                </label>
                <input type="file" id="reference-files" name="reference-files[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                <p class="file-hint">Supported: PDF, JPG, PNG (Max 10MB each)</p>
            </div>
            <div id="files-preview-container"></div>
            <div id="files-analysis"></div>
        </div>
    </div>

    <!-- Section-wise Question Configuration with Toggle -->
    <div class="config-section">
        <div class="section-toggle-header" id="toggle-question-config">
            <h3>📝 Section-wise Question Configuration</h3>
            <span class="toggle-icon">▼</span>
        </div>
        
        <div id="question-config-content" style="display: none;">
            <!-- Section A -->
            <div class="section-config">
                <div class="section-header-config">
                    <h4>Section A - Objective (1 Mark Each)</h4>
                    <span class="section-total" id="section-a-total">0 marks</span>
                </div>
                <div class="question-types-grid">
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="mcq" data-marks="1"> MCQ</label>
                        <input type="number" id="mcq-count" class="count-input" value="0" min="0" max="15" data-marks="1">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="fill_blanks" data-marks="1"> Fill in Blanks</label>
                        <input type="number" id="fill-blanks-count" class="count-input" value="0" min="0" max="15" data-marks="1">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="true_false" data-marks="1"> True/False</label>
                        <input type="number" id="true-false-count" class="count-input" value="0" min="0" max="15" data-marks="1">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="very_short_answer" data-marks="1"> Very Short Answer</label>
                        <input type="number" id="very-short-answer-count" class="count-input" value="0" min="0" max="15" data-marks="1">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="assertion_reason" data-marks="1"> Assertion-Reason</label>
                        <input type="number" id="assertion-reason-count" class="count-input" value="0" min="0" max="15" data-marks="1">
                    </div>
                </div>
            </div>

            <!-- Section B -->
            <div class="section-config">
                <div class="section-header-config">
                    <h4>Section B - Short Answer I (2 Marks Each)</h4>
                    <span class="section-total" id="section-b-total">0 marks</span>
                </div>
                <div class="question-types-grid">
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="short_answer" data-marks="2"> Short Answer</label>
                        <input type="number" id="short-answer-count" class="count-input" value="0" min="0" max="15" data-marks="2">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="numerical" data-marks="2"> Numerical</label>
                        <input type="number" id="numerical-count" class="count-input" value="0" min="0" max="15" data-marks="2">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="give_reason" data-marks="2"> Give Reason</label>
                        <input type="number" id="give-reason-count" class="count-input" value="0" min="0" max="15" data-marks="2">
                    </div>
                </div>
            </div>

            <!-- Section C -->
            <div class="section-config">
                <div class="section-header-config">
                    <h4>Section C - Short Answer II (3 Marks Each)</h4>
                    <span class="section-total" id="section-c-total">0 marks</span>
                </div>
                <div class="question-types-grid">
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="short_answer_3" data-marks="3"> Short Answer (3m)</label>
                        <input type="number" id="short-answer-3-count" class="count-input" value="0" min="0" max="15" data-marks="3">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="numerical_3" data-marks="3"> Numerical (3m)</label>
                        <input type="number" id="numerical-3-count" class="count-input" value="0" min="0" max="15" data-marks="3">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="diagram_based" data-marks="3"> Diagram Based</label>
                        <input type="number" id="diagram-based-count" class="count-input" value="0" min="0" max="15" data-marks="3">
                    </div>
                </div>
            </div>

            <!-- Section D -->
            <div class="section-config">
                <div class="section-header-config">
                    <h4>Section D - Long Answer (5 Marks Each)</h4>
                    <span class="section-total" id="section-d-total">0 marks</span>
                </div>
                <div class="question-types-grid">
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="long_answer" data-marks="5"> Long Answer</label>
                        <input type="number" id="long-answer-count" class="count-input" value="0" min="0" max="10" data-marks="5">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="matrix_match" data-marks="4"> Matrix Match</label>
                        <input type="number" id="matrix-match-count" class="count-input" value="0" min="0" max="5" data-marks="4">
                    </div>
                </div>
            </div>

            <!-- Section E -->
            <div class="section-config">
                <div class="section-header-config">
                    <h4>Section E - Case Study (4 Marks Each)</h4>
                    <span class="section-total" id="section-e-total">0 marks</span>
                </div>
                <div class="question-types-grid">
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="case_study" data-marks="4"> Case Study</label>
                        <input type="number" id="case-study-count" class="count-input" value="0" min="0" max="5" data-marks="4">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="source_based" data-marks="4"> Source Based</label>
                        <input type="number" id="source-based-count" class="count-input" value="0" min="0" max="5" data-marks="4">
                    </div>
                    <div class="question-type-item">
                        <label><input type="checkbox" name="question-type" value="map_based" data-marks="4"> Map Based</label>
                        <input type="number" id="map-based-count" class="count-input" value="0" min="0" max="5" data-marks="4">
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Marks Display -->
        <div class="total-marks-display">
            <span class="total-label">Total Marks:</span>
            <span class="total-value" id="total-marks-value">0</span>
        </div>
    </div>

    <!-- Generate Button -->
    <button id="generate-assignment">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
            <line x1="12" y1="18" x2="12" y2="12"/>
            <line x1="9" y1="15" x2="15" y2="15"/>
        </svg>
        Generate Question Paper
    </button>

    <!-- Progress Bar Container -->
    <div id="progress-container">
        <div class="progress-bar-wrapper">
            <div class="progress-bar-container">
                <div class="progress-bar" id="progress-bar"></div>
            </div>
            <div class="progress-info">
                <span class="progress-percentage" id="progress-percentage">0%</span>
                <span class="progress-message" id="progress-message">Initializing...</span>
            </div>
        </div>
    </div>

    <!-- Tab Container for Results -->
    <div class="tab-container" id="tab-container">
        <div class="tab-navigation">
            <button class="tab-button active" data-tab="test-tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                </svg>
                Question Paper
            </button>
            <button class="tab-button" data-tab="answer-tab">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3 8-8"/>
                    <path d="M21 12c0 5-4 9-9 9s-9-4-9-9 4-9 9-9c1.5 0 2.9.4 4.1 1"/>
                </svg>
                Answer Key & Marking Scheme
            </button>
        </div>

        <div class="tab-content active" id="test-tab">
            <div id="assignment-output"></div>
            <div id="assignment-actions">
                <button id="copy-assignment">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy
                </button>
                <button id="print-assignment">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print
                </button>
                <button id="clear-assignment">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6L17.5 20a2 2 0 0 1-2 2H8.5a2 2 0 0 1-2-2L5 6"></path>
                    </svg>
                    Clear
                </button>
                <button id="save-assignment">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save
                </button>
            </div>
        </div>

        <div class="tab-content" id="answer-tab">
            <div id="answer-sheet-output"></div>
            <div id="answer-sheet-actions">
                <button id="copy-answer-sheet">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy
                </button>
                <button id="print-answer-sheet">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print
                </button>
                <button id="save-answer-sheet">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save
                </button>
            </div>
        </div>
    </div>
</div>