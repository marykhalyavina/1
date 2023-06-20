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
 * plagkh Plagiarism Plugin - Handle Resubmit Files
 * @package   plagiarism_plagkh
 * @copyright 2022 plagkh
 * @author    Gil Cohen <gilc@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace plagiarism_plagkh\task;

use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/plagiarism/plagkh/constants/plagiarism_plagkh.constants.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/enums/plagiarism_plagkh_enums.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_comms.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_httpclient.class.php');

/**
 * plagkh Plagiarism Plugin - Handle Resubmit Files
 */
class plagiarism_plagkh_requestsqueue extends \core\task\scheduled_task {
    /**
     * Get scheduler name, this will be shown to admins on schedulers dashboard.
     */
    public function get_name() {
        return get_string('clsendrequestqueue', 'plagiarism_plagkh');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        $this->handle_queued_requests();
    }

    /**
     * Handle and change the score of resubmitted files.
     */
    private function handle_queued_requests() {
        global $DB;

        $successrequestsids = array();
        $failedrequests = array();

        $canloadmoredata = true;
        $startqueryfrom = 0;

        while ($canloadmoredata) {
            /* Get all the rows, max 100, ascending by creation date first (let the old ones execute first),
            that have less then 5 attempts*/
            $queuedrequests = $DB->get_records_select(
                'plagiarism_plagkh_request',
                'total_retry_attempts < ?',
                array(PLAGIARISM_plagkh_MAX_RETRY),
                'created_date ASC',
                '*',
                $startqueryfrom,
                PLAGIARISM_plagkh_CRON_MAX_DATA_LOOP
            );
            $canloadmoredata = count($queuedrequests) == PLAGIARISM_plagkh_CRON_MAX_DATA_LOOP;

            if (count($queuedrequests) == 0 || !\plagiarism_plagkh_comms::test_plagkh_connection('scheduler_task')) {
                break;
            }

            foreach ($queuedrequests as $item) {
                try {
                    // Send the request to the server.
                    $url = \plagiarism_plagkh_comms::plagkh_api_url() . $item->endpoint;
                    \plagiarism_plagkh_http_client::execute($item->verb, $url, $item->require_auth, $item->data);
                    $successrequestsids[] = $item->id;
                } catch (\Exception $e) {
                    $item->fail_message = $e->getMessage();
                    $item->total_retry_attempts = $item->total_retry_attempts + 1;
                    $failedrequests[] = $item;
                }
            }
            $this->update_queued_request($failedrequests);
            $startqueryfrom = $startqueryfrom + PLAGIARISM_plagkh_CRON_MAX_DATA_LOOP;
        }
        // Delete successfully queued requests.
        if (count($successrequestsids) > 0) {
            $this->delete_queued_request($successrequestsids);
        }
    }

    /**
     * Delete by batches all the request that sent successfuly.
     * @param array $successrequestsids list of requests ids to delete.
     */
    private function delete_queued_request($successrequestsids) {
        global $DB;
        $batchsize = 100;

        for ($i = 0; $i < count($successrequestsids); $i += $batchsize) {
            $batchids = array_slice($successrequestsids, $i, $batchsize);
            if (count($batchids) > 0) {
                if (!$DB->delete_records_list('plagiarism_plagkh_request', 'id', $batchids)) {
                    \plagiarism_plagkh_logs::add(
                        "failed to delete all success queued request",
                        "DELETE_RECORD_FAILED"
                    );
                }
            }
        }
    }

    /**
     * Update all the failed requests that was failed again.
     * @param array $failedrequests array of requests recorde to update.
     */
    private function update_queued_request(&$failedrequests) {
        global $DB;
        if (count($failedrequests) > 0) {
            foreach ($failedrequests as $request) {
                if (!$DB->update_record('plagiarism_plagkh_request', $request)) {
                    \plagiarism_plagkh_logs::add(
                        "failed to update database record for cmid: " .
                            $request->cmid . $request->verb . ", to " . $request->enpoint,
                        "UPDATE_RECORD_FAILED"
                    );
                }
            }
            $failedrequests = array();
        }
    }
}
