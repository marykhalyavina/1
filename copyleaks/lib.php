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
 * Contains Plagiarism plugin specific functions called by Modules.
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Get global class.
global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');

// Get helper methods.
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_pluginconfig.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_moduleconfig.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/constants/plagiarism_plagkh.constants.php');

require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_assignmodule.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_utils.class.php');

require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_comms.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_authexception.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_exception.class.php');

require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_submissiondisplay.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');
/**
 * Contains Plagiarism plugin specific functions called by Modules.
 */
class plagiarism_plugin_plagkh extends plagiarism_plugin {
    /**
     * hook to allow plagiarism specific information to be displayed beside a submission
     * @param array $linkarray contains all relevant information for the plugin to generate a link
     * @return string displayed output
     */
    public function get_links($linkarray) {
        return plagiarism_plagkh_submissiondisplay::output($linkarray);
    }

    /**
     * hook to save plagiarism specific settings on a module settings page
     * @param stdClass $data form data
     */
    public function save_form_elements($data) {
        // Check if plugin is configured and enabled.
        if (empty($data->modulename) || !plagiarism_plagkh_pluginconfig::is_plugin_configured('mod_' . $data->modulename)) {
            return;
        }

        // Save settings to plagkh.
        $cl = new plagiarism_plagkh_comms();
        $updatedata = array(
            'tempCourseModuleId' => isset($data->plagiarism_plagkh_tempcmid) ? $data->plagiarism_plagkh_tempcmid : null,
            'courseModuleId' => $data->coursemodule,
            'name' => $data->name,
            'moduleName' => $data->modulename,
        );
        $cl->upsert_course_module($updatedata);

        try {
            // Get plagkh api course module settings.
            $cl = new plagiarism_plagkh_comms();

            plagiarism_plagkh_moduleconfig::set_module_config(
                $data->coursemodule,
                $data->plagiarism_plagkh_enable,
                isset($data->plagiarism_plagkh_draftsubmit) ? $data->plagiarism_plagkh_draftsubmit : 0,
                isset($data->plagiarism_plagkh_reportgen) ? $data->plagiarism_plagkh_reportgen : 0,
                $data->plagiarism_plagkh_allowstudentaccess
            );
        } catch (plagiarism_plagkh_exception $ex) {
            $errormessage = get_string('clfailtosavedata', 'plagiarism_plagkh');
            plagiarism_plagkh_logs::add($errormessage . ': ' . $ex->getMessage(), 'API_ERROR');
            throw new moodle_exception($errormessage);
        } catch (plagiarism_plagkh_auth_exception $ex) {
            throw new moodle_exception(get_string('clinvalidkeyorsecret', 'plagiarism_plagkh'));
        }
    }

    /**
     * If plugin is enabled then Show the plagkh settings form.
     *
     * TODO: This code needs to be moved for 4.3 as the method will be completely removed from core.
     * See https://tracker.moodle.org/browse/MDL-67526
     *
     * @param MoodleQuickForm $mform
     * @param stdClass $context
     * @param string $modulename
     */
    public function get_form_elements_module($mform, $context, $modulename = "") {
        global $DB, $CFG;
        // This is a bit of a hack and untidy way to ensure the form elements aren't displayed,
        // twice. This won't be needed once this method goes away.
        // TODO: Remove once this method goes away.
        static $settingsdisplayed;
        if ($settingsdisplayed) {
            return;
        }

        if (has_capability('plagiarism/plagkh:enable', $context)) {

            // Return no form if the plugin isn't configured or not enabled.
            if (empty($modulename) || !plagiarism_plagkh_pluginconfig::is_plugin_configured($modulename)) {
                return;
            }

            // plagkh Settings.
            $mform->addElement(
                'header',
                'plagiarism_plagkh_defaultsettings',
                get_string('clscoursesettings', 'plagiarism_plagkh')
            );

            // Database settings.
            $mform->addElement(
                'advcheckbox',
                'plagiarism_plagkh_enable',
                get_string('clenable', 'plagiarism_plagkh')
            );

            // Add draft submission properties only if exists.
            if ($mform->elementExists('submissiondrafts')) {
                $mform->addElement(
                    'advcheckbox',
                    'plagiarism_plagkh_draftsubmit',
                    get_string("cldraftsubmit", "plagiarism_plagkh")
                );
                $mform->addHelpButton(
                    'plagiarism_plagkh_draftsubmit',
                    'cldraftsubmit',
                    'plagiarism_plagkh'
                );
                $mform->disabledIf(
                    'plagiarism_plagkh_draftsubmit',
                    'submissiondrafts',
                    'eq',
                    0
                );
            }

            // Add due date properties only if exists.
            if ($mform->elementExists('duedate')) {
                $genoptions = array(
                    0 => get_string('clgenereportimmediately', 'plagiarism_plagkh'),
                    1 => get_string('clgenereportonduedate', 'plagiarism_plagkh')
                );
                $mform->addElement(
                    'select',
                    'plagiarism_plagkh_reportgen',
                    get_string("clreportgenspeed", "plagiarism_plagkh"),
                    $genoptions
                );
            }

            $mform->addElement(
                'advcheckbox',
                'plagiarism_plagkh_allowstudentaccess',
                get_string('clallowstudentaccess', 'plagiarism_plagkh')
            );

            $cmid = optional_param('update', null, PARAM_INT);
            $savedvalues = $DB->get_records_menu('plagiarism_plagkh_config', array('cm' => $cmid), '', 'name,value');
            if (count($savedvalues) > 0) {
                // Add check for a new Course Module (for lower versions).
                $mform->setDefault(
                    'plagiarism_plagkh_enable',
                    isset($savedvalues['plagiarism_plagkh_enable']) ? $savedvalues['plagiarism_plagkh_enable'] : 0
                );

                $draftsubmit = isset($savedvalues['plagiarism_plagkh_draftsubmit']) ?
                    $savedvalues['plagiarism_plagkh_draftsubmit'] : 0;

                $mform->setDefault('plagiarism_plagkh_draftsubmit', $draftsubmit);
                if (isset($savedvalues['plagiarism_plagkh_reportgen'])) {
                    $mform->setDefault('plagiarism_plagkh_reportgen', $savedvalues['plagiarism_plagkh_reportgen']);
                }
                if (isset($savedvalues['plagiarism_plagkh_allowstudentaccess'])) {
                    $mform->setDefault(
                        'plagiarism_plagkh_allowstudentaccess',
                        $savedvalues['plagiarism_plagkh_allowstudentaccess']
                    );
                }
            } else {
                $mform->setDefault('plagiarism_plagkh_enable', false);
                $mform->setDefault('plagiarism_plagkh_draftsubmit', 0);
                $mform->setDefault('plagiarism_plagkh_reportgen', 0);
                $mform->setDefault('plagiarism_plagkh_allowstudentaccess', 0);
            }

            $settingslinkparams = "?";
            $addparam = optional_param('add', null, PARAM_TEXT);
            $courseid = optional_param('course', 0, PARAM_INT);
            $isnewactivity = isset($addparam) && $addparam != "0";
            if ($isnewactivity) {
                $cmid = plagiarism_plagkh_utils::get_plagkh_temp_course_module_id("$courseid");
                $mform->addElement(
                    'hidden',
                    'plagiarism_plagkh_tempcmid',
                    "$cmid"

                );
                // Need to set type for Moodle's older version.
                $mform->setType('plagiarism_plagkh_tempcmid', PARAM_INT);
                $settingslinkparams = $settingslinkparams . "isnewactivity=$isnewactivity&courseid=$courseid&";
            }

            $settingslinkparams = $settingslinkparams . "cmid=$cmid&modulename=$modulename";

            /*$btn = plagiarism_plagkh_utils::get_plagkh_settings_button_link($settingslinkparams, false, $cmid);
            $mform->addElement('html', $btn);*/

            $settingsdisplayed = true;
        }
    }

    /**
     * hook to allow a disclosure to be printed notifying users what will happen with their submission
     * @param int $cmid - course module id
     * @return string
     */
    public function print_disclosure($cmid) {
        global $DB, $USER;

        // Get course module.
        $cm = get_coursemodule_from_id('', $cmid);

        // Get course module plagkh settings.
        $clmodulesettings = $DB->get_records_menu(
            'plagiarism_plagkh_config',
            array('cm' => $cmid),
            '',
            'name,value'
        );

        // Check if plagkh plugin is enabled for this module.
        $moduleclenabled = plagiarism_plagkh_pluginconfig::is_plugin_configured('mod_' . $cm->modname);
        if (empty($clmodulesettings['plagiarism_plagkh_enable']) || empty($moduleclenabled)) {
            return "";
        }

        $config = plagiarism_plagkh_pluginconfig::admin_config();

        $isuseragreed = plagiarism_plagkh_dbutils::is_user_eula_uptodate($USER->id);

        if (!$isuseragreed) {
            if (isset($config->plagiarism_plagkh_studentdisclosure)) {
                $clstudentdisclosure = $config->plagiarism_plagkh_studentdisclosure;
            } else {
                $clstudentdisclosure = get_string('clstudentdisclosuredefault', 'plagiarism_plagkh');
            }
        } else {
            $clstudentdisclosure = get_string('clstudentdagreedtoeula', 'plagiarism_plagkh');
        }

        $contents = format_text($clstudentdisclosure, FORMAT_MOODLE, array("noclean" => true));
        if (!$isuseragreed) {
            $checkbox = "<input type='checkbox' id='cls_student_disclosure'>" .
                "<label for='cls_student_disclosure' class='plagkh-student-disclosure-checkbox'>$contents</label>";
            $output = html_writer::tag('div', $checkbox, array('class' => 'plagkh-student-disclosure '));
            $output .= html_writer::tag(
                'script',
                "(function disableInput() {" .
                    "setTimeout(() => {" .
                    "var checkbox = document.getElementById('cls_student_disclosure');" .
                    "var btn = document.getElementById('id_submitbutton');" .
                    "btn.disabled = true;" .
                    "var intrval = setInterval(() => {" .
                    "if(checkbox.checked){" .
                    "btn.disabled = false;" .
                    "}else{" .
                    "btn.disabled = true;" .
                    "}" .
                    "}, 1000)" .
                    "}, 500);" .
                    "}());",
                null
            );
        } else {
            $output = html_writer::tag('div', $contents, array('class' => 'plagkh-student-disclosure'));
        }

        return $output;
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */
    public function update_status($course, $cm) {
        // Called at top of submissions/grading pages - allows printing of admin style links or updating status.
    }
}

/**
 * Add the plagkh settings form to an add/edit activity page.
 *
 * @param moodleform_mod $formwrapper
 * @param MoodleQuickForm $mform
 * @return type
 */
/**
 * @var mixed $course
 */
function plagiarism_plagkh_coursemodule_standard_elements($formwrapper, $mform) {
    $plagkhplugin = new plagiarism_plugin_plagkh();
    $course = $formwrapper->get_course();
    $context = context_course::instance($course->id);
    $modulename = $formwrapper->get_current()->modulename;

    $plagkhplugin->get_form_elements_module(
        $mform,
        $context,
        isset($modulename) ? 'mod_' . $modulename : ''
    );
}

/**
 * Handle saving data from the plagkh settings form.
 *
 * @param stdClass $data
 * @param stdClass $course
 */
function plagiarism_plagkh_coursemodule_edit_post_actions($data, $course) {
    $plagkhplugin = new plagiarism_plugin_plagkh();

    $plagkhplugin->save_form_elements($data);

    return $data;
}
