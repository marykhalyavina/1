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
 * settings.php - allows the admin to configure the plugin
 * @package   plagiarism_plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @copyright 2021 plagkh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/plagiarismlib.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/lib.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/forms/plagiarism_plagkh_adminform.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');

require_login();

admin_externalpage_setup('plagiarismplagkh');

$context = context_system::instance();

require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

$qpselectedtabid = optional_param('tab', "plagkhconfiguration", PARAM_ALPHA);
$qpdate = optional_param('date', null, PARAM_ALPHANUMEXT);

$plagkhsetupform = new plagiarism_plagkh_adminform();

if ($plagkhsetupform->is_cancelled()) {
    redirect(new moodle_url('/admin/category.php?category=plagiarism'));
}

$pagetabs = array();
$pagetabs[] = new tabobject(
    'plagkhconfiguration',
    'settings.php',
    get_string('clpluginconfigurationtab', 'plagiarism_plagkh'),
    get_string('clpluginconfigurationtab', 'plagiarism_plagkh'),
    false
);

$pagetabs[] = new tabobject(
    'plagkhlogs',
    'settings.php?tab=plagkhlogs',
    get_string('cllogstab', 'plagiarism_plagkh'),
    get_string('cllogstab', 'plagiarism_plagkh'),
    false
);

switch ($qpselectedtabid) {
    case 'plagkhlogs':
        if (!is_null($qpdate)) {
            plagiarism_plagkh_logs::displaylogs($qpdate);
        } else {
            echo $OUTPUT->header();
            $pagetabs[1]->selected = true;
            echo $OUTPUT->tabtree($pagetabs);
            echo $OUTPUT->heading(get_string('cllogsheading', 'plagiarism_plagkh'));
            plagiarism_plagkh_logs::displaylogs();
        }
        break;
    default:
        echo $OUTPUT->header();
        $pagetabs[0]->selected = true;
        echo $OUTPUT->tabtree($pagetabs);
        // Form data save flow.
        if (($data = $plagkhsetupform->get_data()) && confirm_sesskey()) {
            $plagkhsetupform->save($data);
            $output = $OUTPUT->notification(get_string('cladminconfigsavesuccess', 'plagiarism_plagkh'), 'notifysuccess');
        }

        // Init form data.
        $plagkhsetupform->init_form_data();

        echo $plagkhsetupform->display();
        break;
}

echo $OUTPUT->footer();
