<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Constants.
define('PLAGIARISM_plagkh_DEFAULT_MODULE_CMID', 0);
$clsupportedsubmissiontypes = array('file', 'text_content', 'forum_post', 'quiz_answer');
define('PLAGIARISM_plagkh_SUPPORTED_SUBMISSION_TYPES', $clsupportedsubmissiontypes);


// Max file size 25mb.
define('PLAGIARISM_plagkh_MAX_FILE_UPLOAD_SIZE', 26214400);
define('PLAGIARISM_plagkh_CRON_QUERY_LIMIT', 100);
define('PLAGIARISM_plagkh_CRON_MAX_DATA_LOOP', 256);
define('PLAGIARISM_plagkh_MAX_FILENAME_LENGTH', 180);
define('PLAGIARISM_plagkh_LOGS_PREFIX', 'log_');



// plagkh support file types.
$plagkhacceptedfiles = array(
    // Textual.
    '.html', '.txt', '.csv', '.rtf', '.xml', '.htm',
    // Non-Textual.
    '.pdf', '.docx', '.doc', '.pptx', '.ppt', '.odt',
    '.chm', '.epub', '.odp', '.ppsx', '.pages', '.xlsx',
    '.xls', '.LaTeX',
    // Source code.
    '.py', '.go', '.cs', '.c', '.h', '.idc', '.cpp', '.hpp',
    '.cpp', '.hpp', '.cc', '.hh', '.java', '.js', '.swift',
    '.rb', '.pl', '.php', '.sh', '.m', '.scala',
    // OCR.
    '.gif', '.png', '.bmp', '.jpg', '.jpeg'
);
define('PLAGIARISM_plagkh_ACCEPTED_FILES', $plagkhacceptedfiles);
define('DEFAULT_DATABASE_plagkhDB_ID', 'DEFAULT_DATABASE_plagkhDB_ID');

// plagkh retry array in seconds.
define('PLAGIARISM_plagkh_RETRY', array(0, 2.5, 3, 5, 10));
define('PLAGIARISM_plagkh_MAX_RETRY', 5);
define('PLAGIARISM_plagkh_DEFUALT_EULA_VERSION', '2023032700');
define('PLAGIARISM_plagkh_EULA_FIELD_NAME', 'plagiarism_plagkh_latesteulaversion ');
