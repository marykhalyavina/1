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
 * module configurations helpers methods
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\check\performance\stats;

/**
 * module configurations helpers methods
 */
class plagiarism_plagkh_moduleconfig {
    /**
     * Get course module db properties
     * @return array Course Module DB Properties
     */
    public static function get_config_db_properties() {
        return array(
            'plagiarism_plagkh_enable',
            'plagiarism_plagkh_draftsubmit',
            'plagiarism_plagkh_reportgen',
            'plagiarism_plagkh_allowstudentaccess'
        );
    }

    /**
     * get course module config by course module id
     * @param string $cmid course module id
     * @return array course module config, returns default config if not found
     */
    public static function get_module_config($cmid) {
        global $DB;

        $result = $DB->get_records_menu(
            'plagiarism_plagkh_config',
            array('cm' => $cmid),
            '',
            'name,value'
        );

        if (!$result) {
            $result = self::get_modules_default_config();
        }

        return $result;
    }

    /**
     * get course module default config
     * @return array course module config
     */
    public static function get_modules_default_config() {
        global $DB;
        $result = $DB->get_records_menu(
            'plagiarism_plagkh_config',
            array('cm' => PLAGIARISM_plagkh_DEFAULT_MODULE_CMID),
            '',
            'name,value'
        );
        if (count($result) > 0) {
            $result['cmid'] = PLAGIARISM_plagkh_DEFAULT_MODULE_CMID;
        }
        return $result;
    }

    /**
     * update course module default config
     * @param string $cmid (optional)
     * @param boolean $enabled (optional)
     * @param boolean $draftssubmit (optional)
     * @param boolean $reportgen (optional)
     * @param boolean $allowstudentaccess (optional)
     */
    public static function set_module_config(
        $cmid = PLAGIARISM_plagkh_DEFAULT_MODULE_CMID,
        $enabled = false,
        $draftssubmit = 0,
        $reportgen = 0,
        $allowstudentaccess = 0
    ) {
        global $DB;

        $default = array();
        $default['plagiarism_plagkh_enable'] = $enabled;
        $default['plagiarism_plagkh_draftsubmit'] = $draftssubmit;
        $default['plagiarism_plagkh_reportgen'] = $reportgen;
        $default['plagiarism_plagkh_allowstudentaccess'] = $allowstudentaccess;

        // Db settings elements name.
        $clcmconfigfields = self::get_config_db_properties();

        // Get saved db settings.
        $saveddefaultvalue = $DB->get_records_menu('plagiarism_plagkh_config', array('cm' => $cmid), '', 'name,value');

        // Save db settings.
        foreach ($clcmconfigfields as $f) {
            if (isset($default[$f])) {
                $savedfield = new stdClass();
                $savedfield->cm = $cmid;
                $savedfield->name = $f;
                $savedfield->value = $default[$f];

                if (!isset($saveddefaultvalue[$f])) {
                    $savedfield->config_hash = $savedfield->cm . "_" . $savedfield->name;
                    if (!$DB->insert_record('plagiarism_plagkh_config', $savedfield)) {
                        throw new moodle_exception(get_string('clinserterror', 'plagiarism_plagkh'));
                    }
                } else {
                    $savedfield->id = $DB->get_field(
                        'plagiarism_plagkh_config',
                        'id',
                        (array(
                            'cm' => $cmid,
                            'name' => $f
                        ))
                    );
                    if (!$DB->update_record('plagiarism_plagkh_config', $savedfield)) {
                        throw new moodle_exception(get_string('clupdateerror', 'plagiarism_plagkh'));
                    }
                }
            }
        }
    }

    /**
     * Check if plagkh plugin is enabled
     * @param string $modulename course module name
     * @param string $cmid course module id
     * @return bool is plagkh plugin enabled
     */
    public static function is_module_enabled($modulename, $cmid) {
        $plagiarismsettings = self::get_module_config($cmid);

        $moduleclenabled = plagiarism_plagkh_pluginconfig::is_plugin_configured('mod_' . $modulename);
        if (empty($plagiarismsettings['plagiarism_plagkh_enable']) || empty($moduleclenabled)) {
            return false;
        }
        return true;
    }

    /**
     * Check if it is possible for students to accept EULA in a specific module
     * @param string $modname module type name
     * @return bool is allowed
     */
    public static function is_allowed_eula_acceptance($modname) {
        $supportedeulamodules = array('assign', 'workshop');
        return in_array($modname, $supportedeulamodules);
    }

    /**
     * @param string $cmid check if the cmid is in the requests queue
     * @return bool
     */
    public static function is_course_module_request_queued($cmid) {
        global $DB;
        $record = $DB->get_record('plagiarism_plagkh_request', ['cmid' => $cmid]);
        return isset($record) && $record;
    }
}
