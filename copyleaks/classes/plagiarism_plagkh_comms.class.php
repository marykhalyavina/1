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

require_once($CFG->dirroot . '/plagiarism/plagkh/lib.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_httpclient.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_pluginconfig.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_dbutils.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/enums/plagiarism_plagkh_enums.php');
/**
 * Used for communications between Moodle and plagkh
 */
class plagiarism_plagkh_comms {
    /** @var stdClass plagkh plugin configurations */
    private $config;

    /** @var string plagkh account secret */
    private $secret;

    /** @var string plagkh account key  */
    private $key;

    /**
     * class constructor
     */
    public function __construct() {
        $this->config = plagiarism_plagkh_pluginconfig::admin_config();
        if (isset($this->config->plagiarism_plagkh_secret) && isset($this->config->plagiarism_plagkh_key)) {
            $this->secret = $this->config->plagiarism_plagkh_secret;
            $this->key = $this->config->plagiarism_plagkh_key;
        }
    }

    /**
     * Submit to plagkh for plagiairsm scan
     * @param string $filepath file path
     * @param string $filename file name
     * @param string $cmid course module id
     * @param string $userid user id
     * @param string $identifier content identifier
     * @param string $submissiontype submission type
     * @return mixed
     */
    public function submit_for_plagiarism_scan(
        string $filepath,
        string $filename,
        string $cmid,
        string $userid,
        string $identifier,
        string $submissiontype
    ) {
        if (isset($this->key) && isset($this->secret)) {
            $coursemodule = get_coursemodule_from_id('', $cmid);
            if (plagiarism_plagkh_dbutils::is_user_eula_uptodate($userid)) {
                $student = get_complete_user_data('id', $userid);
                $paramsmerge = (array)[
                    'fileName' => $filename,
                    'courseModuleId' => $cmid,
                    'moodleUserId' => $userid,
                    'identifier' => $identifier,
                    'submissionType' => $submissiontype,
                    'userEmail' => $student->email,
                    'userFullName' => $student->firstname . " " . $student->lastname,
                    'moduleName' => $coursemodule->name,
                    'courseId' => $coursemodule->course,
                    'courseName' => (get_course($coursemodule->course))->fullname
                ];
            } else {
                $paramsmerge = (array)[
                    'fileName' => $filename,
                    'courseModuleId' => $cmid,
                    'moodleUserId' => $userid,
                    'identifier' => $identifier,
                    'submissionType' => $submissiontype
                ];
            }

            $mimetype = mime_content_type($filepath);
            if (class_exists('CURLFile')) {
                $paramsmerge['file'] = new \CURLFile($filepath, $mimetype, $filename);
            } else {
                $paramsmerge['file'] = '@' . $filepath;
            }

            $result = plagiarism_plagkh_http_client::execute(
                'POST',
                $this->plagkh_api_url() . "/api/moodle/plugin/" . $this->key . "/task/submit-for-scan",
                true,
                $paramsmerge,
                false,
                "multipart/form-data"
            );
            return $result;
        }
    }

    /**
     * get the plagkh API scan instances for submissions
     * @param array $submissionsinstances
     * @return array a list of plagkh scan instances for files
     */
    public function get_plagiarism_scans_instances(array $submissionsinstances) {
        if (isset($this->key) && isset($this->secret)) {

            $params = (array)[
                'instances' => $submissionsinstances,
            ];

            $result = plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $this->plagkh_api_url() . "/api/moodle/plugin/" . $this->key . "/task/scan-instances",
                true,
                json_encode($params)
            );

            return $result;
        }
    }

    /**
     * Get resubmit reports ids from lms server
     * @param string $cursor plagkh db cursor
     * @return object $result an array of resubmitted ids and new ids that rescanned
     */
    public function get_resubmit_reports_ids($cursor) {
        if (isset($this->key) && isset($this->secret)) {
            $reqbody = (array)[
                'cursor' => $cursor
            ];
            $result = plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $this->plagkh_api_url() . "/api/moodle/plugin/" . $this->key . "/task/resubmit-scans",
                true,
                json_encode($reqbody)
            );
            return $result;
        }
    }

    /**
     * send request to delete resubmitted id to plagkh server
     * @param array $ids plagkh report scan ids
     */
    public function delete_resubmitted_ids(array $ids) {
        if (isset($this->key) && isset($this->secret)) {
            $reqbody = (array)[
                'ids' => $ids
            ];
            plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $this->plagkh_api_url() . "/api/moodle/plugin/" . $this->key . "/task/resubmit-scans/delete",
                true,
                json_encode($reqbody)
            );
        }
    }

    /**
     * request access for plagkh report
     * @param string $scanid plagkh report scan id
     * @param boolean $isinstructor plagkh report scan id
     * @return string a JWT to access student report only
     */
    public function request_access_for_report(string $scanid, $isinstructor) {
        if ($isinstructor == 0) {
            $isinstructor = -1;
        }

        if (isset($this->key) && isset($this->secret)) {
            $result = plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $this->plagkh_api_url() . "/api/moodle/" . $this->key .
                    "/report/" . $scanid . "/" . $isinstructor . "/request-access",
                true
            );

            return $result->token;
        }
    }

    /**
     * request access for plagkh report.
     * @param boolean $role Moodle user's role.
     * @param array $breadcrumbs Moodle breadcrumbs.
     * @param array $name of the activity type.
     * @param array $coursemodulename of the activity.
     * @return string $cmid for the settings and access.
     */
    public function request_access_for_settings($role, $breadcrumbs, $name = '', $coursemodulename = '', $cmid = 0) {
        if (isset($this->key) && isset($this->secret)) {
            $reqbody = (array)[
                'breadcrumbs' => $breadcrumbs,
                'name' => $name,
                'courseModuleName' => $coursemodulename,
                'accessRole' => $role
            ];
            $url = $this->plagkh_api_url() . "/api/moodle/" . $this->key . "/settings/request-access";
            if (isset($cmid)) {
                $url = $url . "/$cmid";
            }
            $result = plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $url,
                true,
                json_encode($reqbody)
            );

            return $result->token;
        }
    }

    /**
     * get plagkh api url.
     * @return string api url if exists, otherwise return null
     */
    public static function plagkh_api_url() {
        $apiurl = get_config('plagiarism_plagkh', 'plagiarism_plagkh_apiurl');
        if (isset($apiurl) && !empty($apiurl)) {
            return $apiurl;
        }

        return "https://lti.plagkh.com";
    }

    /**
     * login to plagkh api and get token
     * this will also get token from db if exisits
     * @param string $apiurl plagkh communication url (optional)
     * @param string $key plagkh API connection key (optional)
     * @param string $secret plagkh API connection secret (optional)
     * @param bool $force true to ignore db cached jwt token (optional)
     * @return string jwt access token for plagkh api
     */
    public static function login_to_plagkh($apiurl = null, $key = null, $secret = null, $force = false) {
        if (!isset($secret) || !isset($key) || !isset($apiurl)) {
            // If key and secret was not passed, try to read them from admin config.
            $config = plagiarism_plagkh_pluginconfig::admin_config();
            if (
                isset($config->plagiarism_plagkh_secret) &&
                isset($config->plagiarism_plagkh_key) &&
                isset($config->plagiarism_plagkh_apiurl)
            ) {
                $secret = $config->plagiarism_plagkh_secret;
                $key = $config->plagiarism_plagkh_key;
                $apiurl = $config->plagiarism_plagkh_apiurl;

                if (!isset($secret) || !isset($key) || !isset($apiurl)) {
                    return null;
                }
            }
        }

        if (!$force) {
            // If not force ,try to get them from cache.
            $config = plagiarism_plagkh_pluginconfig::admin_config();
            if (isset($config->plagiarism_plagkh_jwttoken)) {
                $result = $config->plagiarism_plagkh_jwttoken;
            }
        }

        if (!isset($result) || $force) {
            // Login to plagkh api and get jwt.
            $reqbody = (array)[
                'secret' => $secret
            ];

            $result = plagiarism_plagkh_http_client::execute_retry(
                'POST',
                $apiurl . "/api/moodle/plugin/" . $key . "/login",
                false,
                $reqbody,
                true
            );

            if ($result) {
                $result = $result->jwt;
                set_config('plagiarism_plagkh_jwttoken', $result, 'plagiarism_plagkh');
            }
        }

        return $result;
    }

    /**
     * Test if server can communicate with plagkh.
     * @param string $context
     * @return bool
     */
    public static function test_plagkh_connection($context) {
        $cl = new plagiarism_plagkh_comms();
        return $cl->test_connection($context);
    }

    /**
     * Test if server can communicate with plagkh.
     * @param string $context
     * @return bool
     */
    public function test_connection($context) {
        try {
            if (isset($this->key) && isset($this->secret)) {
                $result = plagiarism_plagkh_http_client::execute_retry(
                    'GET',
                    $this->plagkh_api_url() . "/api/moodle/plugin/" . $this->key . "/test-connection?source=" . $context,
                    true
                );
                if (isset($result) && isset($result->eulaVersion)) {
                    plagiarism_plagkh_dbutils::update_plagkh_eula_version($result->eulaVersion);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            if ($context == 'scheduler_task') {
                $errormsg = get_string('cltaskfailedconnecting', 'plagiarism_plagkh', $e->getMessage());
                plagiarism_plagkh_logs::add($errormsg, 'API_ERROR');
            }
            return false;
        }
    }

    /**
     * Update course module temp id at plagkh server.
     * @param array $data
     */
    public function upsert_course_module($data) {
        $endpoint = "/api/moodle/plugin/$this->key/upsert-module";
        $verb = 'POST';
        try {
            plagiarism_plagkh_http_client::execute(
                $verb,
                $this->plagkh_api_url() . $endpoint,
                true,
                json_encode($data)
            );
        } catch (\Exception $e) {
            plagiarism_plagkh_dbutils::queued_failed_request(
                $data['courseModuleId'],
                $endpoint,
                $data,
                plagiarism_plagkh_priority::HIGH,
                $e->getMessage(),
                $verb
            );
        }
    }

    /**
     * Update course module temp id at plagkh server.
     * @param array $data
     * @return object all the user ids that was updated succesfully in plagkh server
     */
    public function upsert_synced_eula($data) {
        $result = plagiarism_plagkh_http_client::execute(
            'POST',
            $this->plagkh_api_url() . "/api/moodle/plugin/$this->key/task/eula-approval-sync",
            true,
            json_encode($data)
        );
        return $result;
    }
}
