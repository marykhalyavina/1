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
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/constants/plagiarism_plagkh.constants.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_dbutils.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_comms.class.php');




/**
 * plagkh Plagiarism Plugin - Handle Resubmit Files
 */
class plagiarism_plagkh_synceulausers extends \core\task\scheduled_task {
    /**
     * Get scheduler name, this will be shown to admins on schedulers dashboard.
     */
    public function get_name() {
        return get_string('clupserteulausers', 'plagiarism_plagkh');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        if (!\plagiarism_plagkh_comms::test_plagkh_connection('scheduler_task')) {
            return;
        }

        $this->handle_synced_users();
    }

    /**
     * Handle and change the score of resubmitted files.
     */
    private function handle_synced_users() {
        global $DB;
        $canloadmoredata = true;
        $limitfrom = 0;
        $condition = array('is_synced' => false);
        $cl = new \plagiarism_plagkh_comms();
        $useridstosync = array();
        $maxdataloadloops = PLAGIARISM_plagkh_CRON_MAX_DATA_LOOP;

        while ($canloadmoredata && (--$maxdataloadloops) > 0) {
            try {
                $eulausers = $DB->get_records(
                    'plagiarism_plagkh_eula',
                    $condition,
                    '',
                    '*',
                    $limitfrom,
                    PLAGIARISM_plagkh_CRON_QUERY_LIMIT
                );

                $recordscount = count($eulausers);
                if ($recordscount == 0) {
                    break;
                }
                $canloadmoredata = $recordscount == PLAGIARISM_plagkh_CRON_QUERY_LIMIT;

                $model = $this->arrange_request_model($eulausers);
                $result = $cl->upsert_synced_eula($model);
                $useridstosync = isset($result->usersIds) ? $result->usersIds : array();
            } catch (\Exception $e) {
                \plagiarism_plagkh_logs::add(
                    "Update eula users tasks failed, " . $e->getMessage(),
                    "UPDATE_RECORD_FAILED"
                );
            }

            if (count($useridstosync) > 0) {
                // Get only the users that theirs ids is return from the plagkh server.
                $eulatosyncarray = array_filter($eulausers, function ($user) use ($useridstosync) {
                    return in_array($user->ci_user_id, $useridstosync);
                });

                foreach ($eulatosyncarray as $eulauser) {
                    $eulauser->is_synced = true;
                    if (!$DB->update_record('plagiarism_plagkh_eula', $eulauser)) {
                        \plagiarism_plagkh_logs::add(
                            "Failed to update synced user: $eulauser->user_id",
                            "UPDATE_RECORD_FAILED"
                        );
                    }
                }
            }

            $limitfrom = $limitfrom + PLAGIARISM_plagkh_CRON_QUERY_LIMIT;
        }
    }

    /**
     * Map the db record to the request's model
     * @param array $eulausers array of db records.
     * @return mixed
     */
    private function arrange_request_model($eulausers) {
        $data = array_map(function ($record) {
            if (isset($record->ci_user_id)) {
                return array(
                    'userid' => $record->ci_user_id,
                    'version' => $record->version,
                    'date' => $record->date
                );
            }
        }, $eulausers);
        $data = array_values($data);
        return array('eulaUsersData' => $data);
    }
}
