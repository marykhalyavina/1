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
 * Task API - https://docs.moodle.org/dev/Task_API
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'plagiarism_plagkh\task\plagiarism_plagkh_updatereports',
        'blocking' => 0,
        'minute' => '*/1',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'plagiarism_plagkh\task\plagiarism_plagkh_resubmittedreports',
        'blocking' => 0,
        'minute' => '*/10',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'plagiarism_plagkh\task\plagiarism_plagkh_sendsubmissions',
        'blocking' => 0,
        'minute' => '*/1',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'plagiarism_plagkh\task\plagiarism_plagkh_requestsqueue',
        'blocking' => 0,
        'minute' => '*/1',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
    array(
        'classname' => 'plagiarism_plagkh\task\plagiarism_plagkh_synceulausers',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*/2',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ),
);
