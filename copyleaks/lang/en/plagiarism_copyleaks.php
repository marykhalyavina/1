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
 * This file containes the translations for English
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname']  = 'plagkh plagiarism plugin';
$string['plagkh'] = 'plagkh';
$string['clstudentdisclosure'] = 'Student disclosure';
$string['clstudentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['clstudentdisclosuredefault']  = '<span>By submitting your files you are agreeing to the plagiarism detection service </span><a target="_blank" href="https://plagkh.com/legal/privacypolicy">privacy policy</a>';
$string['clstudentdagreedtoeula']  = '<span>You have already agreed to the plagiarism detection service </span><a target="_blank" href="https://plagkh.com/legal/privacypolicy">privacy policy</a>';
$string['cladminconfigsavesuccess'] = 'plagkh plagiarism settings was saved successfully.';
$string['clpluginconfigurationtab'] = 'Configurations';
$string['cllogstab'] = 'Logs';
$string['cladminconfig'] = 'plagkh plagiarism plugin configuration';
$string['clpluginintro'] = 'The plagkh plagiarism checker is a comprehensive and accurate solution that helps teachers and students check if their content is original.<br>For more information on how to setup and use the plugin please check <a target="_blank" href="https://lti.plagkh.com/guides/select-moodle-integration">our guides</a>.</br></br></br>';
$string['clenable'] = 'Enable plagkh';
$string['clenablemodulefor'] = 'Enable plagkh for {$a}';
$string['claccountconfig'] = "plagkh account configuration";
$string['clapiurl'] = 'plagkh API-URL';
$string['claccountkey'] = "plagkh key";
$string['claccountsecret'] = "plagkh secret";
$string['clallowstudentaccess'] = 'Allow students access to plagiarism reports';
$string['clinvalidkeyorsecret'] = 'Invalid key or secret';
$string['clfailtosavedata'] = 'Fail to save plagkh data';
$string['clplagiarised'] = 'Similarity score';
$string['clopenreport'] = 'Click to open plagkh report';
$string['clscoursesettings'] = 'plagkh settings';
$string['clupdateerror'] = 'Error while trying to update records in database';
$string['clinserterror'] = 'Error while trying to insert records to database';
$string['clsendqueuedsubmissions'] = "plagkh plagiarism plugin - handle queued files";
$string['clsendresubmissionsfiles'] = "plagkh plagiarism plugin - handle resubmitted results";
$string['clsendrequestqueue'] = "plagkh plagiarism plugin - handle retry queued requests";
$string['clupserteulausers'] = "plagkh plagiarism plugin - handle upsert eula acceptance users";
$string['clupdatereportscores'] = "plagkh plagiarism plugin - handle plagiairsm check similarity score update";
$string['cldraftsubmit'] = "Submit files only when students click the submit button";
$string['cldraftsubmit_help'] = "This option is only available if 'Require students to click the submit button' is Yes";
$string['clreportgenspeed'] = 'When to generate report?';
$string['clgenereportimmediately'] = 'Generate reports immediately';
$string['clgenereportonduedate'] = 'Generate reports on due date';
$string['cltaskfailedconnecting'] = 'Connection to plagkh can not be established, error: {$a}';
$string['clapisubmissionerror'] = 'plagkh has returned an error while trying to send file for submission - ';
$string['clcheatingdetected'] = 'Cheating detected, Open report to learn more';
$string['clcheatingdetectedtxt'] = 'Cheating detected';
$string['clreportpagetitle'] = 'plagkh report';
$string['clscansettingspagebtntxt'] = 'Edit scan settings';
$string['clmodulescansettingstxt'] = "Edit scan settings";
$string['cldisablesettingstooltip'] = "Working on syncing data to plagkh...";
$string['clopenfullscreen'] = 'Open in full screen';
$string['cllogsheading'] = 'Logs';
$string['clpoweredbyplagkh'] = 'Powered by plagkh';
$string['clplagiarisefailed'] = 'Failed';
$string['clplagiarisescanning'] = 'Scanning for plagiarism...';
$string['clplagiarisequeued'] = 'Scheduled for plagiarism scan at {$a}';
$string['cldisabledformodule'] = 'plagkh plugin is disabled for this module.';
$string['clnopageaccess'] = 'You dont have access to this page.';
$string['privacy:metadata:core_files'] = 'plagkh stores files that have been uploaded to Moodle to form a plagkh submission.';
$string['privacy:metadata:plagiarism_plagkh_files'] = 'Information that links a Moodle submission to a plagkh submission.';
$string['privacy:metadata:plagiarism_plagkh_files:userid'] = 'The ID of the user who is the owner of the submission.';
$string['privacy:metadata:plagiarism_plagkh_files:submitter'] = 'The ID of the user who has made the submission.';
$string['privacy:metadata:plagiarism_plagkh_files:similarityscore'] = 'The similarity score of the submission.';
$string['privacy:metadata:plagiarism_plagkh_files:lastmodified'] = 'A timestamp indicating when the user last modified their submission.';
$string['privacy:metadata:plagiarism_plagkh_client'] = 'In order to integrate with a plagkh, some user data needs to be exchanged with plagkh.';
$string['privacy:metadata:plagiarism_plagkh_client:module_id'] = 'The module id is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:module_name'] = 'The module name is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:module_type'] = 'The module type is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:module_creationtime'] = 'The module creation time is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:submittion_userId'] = 'The submission userId is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:submittion_name'] = 'The submission name is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:submittion_type'] = 'The submission type is sent to plagkh for identification purposes.';
$string['privacy:metadata:plagiarism_plagkh_client:submittion_content'] = 'The submission content is sent to plagkh for scan processing.';
