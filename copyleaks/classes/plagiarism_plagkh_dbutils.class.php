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
 * plagkh_comms.class.php - used for communications between Moodle and plagkh
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/plagiarism/plagkh/constants/plagiarism_plagkh.constants.php');


/**
 * Functions that can be used in multiple places
 */
class plagiarism_plagkh_dbutils {

    /**
     * Save the failed request to the table queue in the data base.
     * @param string $cmid
     * @param string $endpoint
     * @param array $data
     * @param int $priority
     * @param string $error
     * @param bool $require_auth
     */
    public static function queued_failed_request($cmid, $endpoint, $data, $priority, $error, $verb, $requireauth = true) {
        global $DB;
        $records = $DB->get_record_select(
            'plagiarism_plagkh_request',
            "cmid = ? AND endpoint = ?",
            array($cmid, $endpoint)

        );

        if (!$records) {
            $request = new stdClass();
            $request->created_date = time();
            $request->cmid = $cmid;
            $request->endpoint = $endpoint;
            $request->total_retry_attempts = 0;
            $request->data = json_encode($data);
            $request->priority = $priority;
            $request->status = plagiarism_plagkh_request_status::FAILED;
            $request->fail_message = $error;
            $request->verb = $verb;
            $request->require_auth = $requireauth;
            if (!$DB->insert_record('plagiarism_plagkh_request', $request)) {
                \plagiarism_plagkh_logs::add(
                    "failed to create new database record queue request for cmid: " .
                        $data["courseModuleId"] . ", endpoint: $endpoint",
                    "INSERT_RECORD_FAILED"
                );
            }
        }
    }

    /**
     * Update current eula version.
     * @param string $version
     */
    public static function update_plagkh_eula_version($version) {
        global $DB;
        $configeula = $DB->get_record(
            'plagiarism_plagkh_config',
            array('cm' => PLAGIARISM_plagkh_DEFAULT_MODULE_CMID, 'name' => PLAGIARISM_plagkh_EULA_FIELD_NAME)
        );

        if ($configeula) {
            $configeula->value = $version;
            if (!$DB->update_record('plagiarism_plagkh_config', $configeula)) {
                \plagiarism_plagkh_logs::add(
                    "Could not update eula version to: $version",
                    "UPDATE_RECORD_FAILED"
                );
            }
        } else {
            $configeula = array(
                'cm' => PLAGIARISM_plagkh_DEFAULT_MODULE_CMID,
                'name' => PLAGIARISM_plagkh_EULA_FIELD_NAME,
                'value' => $version,
                'config_hash' => PLAGIARISM_plagkh_DEFAULT_MODULE_CMID . "_" . PLAGIARISM_plagkh_EULA_FIELD_NAME
            );
            if (!$DB->insert_record('plagiarism_plagkh_config', $configeula)) {
                throw new moodle_exception(get_string('clinserterror', 'plagiarism_plagkh'));
            }
        }
    }

    /**
     * Check if the last eula of the user is the same as the last eula version.
     * @param string userid check eula version by user Moodle id.
     * @return bool
     */
    public static function is_user_eula_uptodate($userid) {
        global $DB;

        $user = $DB->get_record('plagiarism_plagkh_users', array('userid' => $userid));
        if (!$user || !isset($user)) {
            return false;
        }

        $version = self::get_plagkh_eula_version();
        return self::is_eula_version_update_by_userid($userid, $version);
    }

    /**
     * Update in plagkh server that the user accepted the current version.
     * @param string userid
     */
    public static function upsert_eula_by_user_id($userid) {
        global $DB;
        $user = $DB->get_record('plagiarism_plagkh_users', array('userid' => $userid));
        $curreulaversion = self::get_plagkh_eula_version();

        if (!$user) {
            if (!$DB->insert_record('plagiarism_plagkh_users', array('userid' => $userid))) {
                \plagiarism_plagkh_logs::add(
                    "failed to insert new database record for : " .
                        "plagiarism_plagkh_users, Cannot create new user record for user $userid",
                    "INSERT_RECORD_FAILED"
                );
            }
        }

        $newusereula = array(
            "ci_user_id" => $userid,
            "version" => $curreulaversion,
            "is_synced" => false,
            "date" => date('Y-m-d H:i:s')
        );

        if (
            !self::is_eula_version_update_by_userid($userid, $curreulaversion)
            && !$DB->insert_record('plagiarism_plagkh_eula', $newusereula)
        ) {
            \plagiarism_plagkh_logs::add(
                "failed to insert new database record for :" .
                    "plagiarism_plagkh_eula, Cannot create new user record eula for user $userid",
                "INSERT_RECORD_FAILED"
            );
        }
    }

    /**
     * return string
     */
    public static function get_plagkh_eula_version() {
        global $DB;
        $record = $DB->get_record(
            'plagiarism_plagkh_config',
            array(
                'cm' => PLAGIARISM_plagkh_DEFAULT_MODULE_CMID,
                'name' => PLAGIARISM_plagkh_EULA_FIELD_NAME
            )
        );
        if ($record) {
            return $record->value;
        }
        return PLAGIARISM_plagkh_DEFUALT_EULA_VERSION;
    }

    /**
     * @param string $userid check by user id if updated.
     * @param string $version id the user id up-to-date the version
     * @return object
     */
    private static function is_eula_version_update_by_userid($userid, $version) {
        global $DB;
        $result = $DB->record_exists_select(
            "plagiarism_plagkh_eula",
            "ci_user_id = ? AND version = ?",
            array($userid, $version)
        );
        return $result;
    }
}
