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
 * plagkh_setupform.class.php - Plugin setup form for plagiarism_plagkh component
 * @package   plagiarism_plagkh
 * @copyright 2021 plagkh
 * @author    Bayan Abuawad <bayana@plagkh.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/plagiarism/plagkh/lib.php');
require_once($CFG->dirroot . '/lib/formslib.php');

require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_comms.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_pluginconfig.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_moduleconfig.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_authexception.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_exception.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_ratelimitexception.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/exceptions/plagiarism_plagkh_undermaintenanceexception.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_logs.class.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/constants/plagiarism_plagkh.constants.php');
require_once($CFG->dirroot . '/plagiarism/plagkh/classes/plagiarism_plagkh_utils.class.php');

/**
 * plagkh admin setup form
 */
class plagiarism_plagkh_adminform extends moodleform {
    /**
     * Define the form
     * */
    public function definition() {
        global $CFG;
        $mform = &$this->_form;

        // Plugin Configurations.
        $mform->addElement(
            'header',
            'plagiarism_plagkh_adminconfigheader',
            get_string('cladminconfig', 'plagiarism_plagkh', null, true)
        );
        $mform->addElement(
            'html',
            get_string('clpluginintro', 'plagiarism_plagkh')
        );

        // Get all modules that support plagiarism plugin.
        $plagiarismmodules = array_keys(core_component::get_plugin_list('mod'));
        $supportedmodules = array('assign', 'forum', 'workshop', 'quiz');
        foreach ($plagiarismmodules as $module) {
            // For now we only support assignments.
            if (in_array($module, $supportedmodules) && plugin_supports('mod', $module, FEATURE_PLAGIARISM)) {
                array_push($supportedmodules, $module);
                $mform->addElement(
                    'advcheckbox',
                    'plagiarism_plagkh_mod_' . $module,
                    get_string('clenablemodulefor', 'plagiarism_plagkh', ucfirst($module == 'assign' ? 'Assignment' : $module))
                );
            }
        }

        $mform->addElement(
            'textarea',
            'plagiarism_plagkh_studentdisclosure',
            get_string('clstudentdisclosure', 'plagiarism_plagkh')
        );
        $mform->addHelpButton(
            'plagiarism_plagkh_studentdisclosure',
            'clstudentdisclosure',
            'plagiarism_plagkh'
        );

        // plagkh Account Configurations.
        $mform->addElement(
            'header',
            'plagiarism_plagkh_accountconfigheader',
            get_string('claccountconfig', 'plagiarism_plagkh')
        );
        $mform->setExpanded('plagiarism_plagkh_accountconfigheader');
        // Thos settings will be save on Moodle database.
        $mform->addElement(
            'text',
            'plagiarism_plagkh_apiurl',
            get_string('clapiurl', 'plagiarism_plagkh')
        );
        $mform->setType('plagiarism_plagkh_apiurl', PARAM_TEXT);
        $mform->addElement(
            'text',
            'plagiarism_plagkh_key',
            get_string('claccountkey', 'plagiarism_plagkh')
        );
        $mform->setType('plagiarism_plagkh_key', PARAM_TEXT);
        $mform->addElement(
            'passwordunmask',
            'plagiarism_plagkh_secret',
            get_string('claccountsecret', 'plagiarism_plagkh')
        );

        if (\plagiarism_plagkh_comms::test_plagkh_connection('admin_settings_page')) {
            $btn = plagiarism_plagkh_utils::get_plagkh_settings_button_link(null, true);
            $mform->addElement('html', $btn);
        }

        $this->add_action_buttons();
    }

    /**
     * form custom validations
     * @param mixed $data
     * @param mixed $files
     */
    public function validation($data, $files) {
        $newconfigsecret = $data["plagiarism_plagkh_secret"];
        $newconfigkey = $data["plagiarism_plagkh_key"];
        $newapiurl = $data["plagiarism_plagkh_apiurl"];

        $config = plagiarism_plagkh_pluginconfig::admin_config();
        if (
            isset($config->plagiarism_plagkh_secret) &&
            isset($config->plagiarism_plagkh_key) &&
            isset($config->plagiarism_plagkh_apiurl)
        ) {
            $secret = $config->plagiarism_plagkh_secret;
            $key = $config->plagiarism_plagkh_key;
            $apiurl = $config->plagiarism_plagkh_apiurl;

            if ($secret != $newconfigsecret || $key != $newconfigkey || $apiurl != $newapiurl) {
                try {
                    $cljwttoken = plagiarism_plagkh_comms::login_to_plagkh($newapiurl, $newconfigkey, $newconfigsecret, true);
                    if (isset($cljwttoken)) {
                        return array();
                    } else {
                        return (array)[
                            "plagiarism_plagkh_secret" => get_string('clinvalidkeyorsecret', 'plagiarism_plagkh')
                        ];
                    }
                } catch (plagiarism_plagkh_exception $ex) {
                    switch ($ex->getCode()) {
                        case 404:
                            return (array)[
                                "plagiarism_plagkh_secret" => get_string('clinvalidkeyorsecret', 'plagiarism_plagkh')
                            ];
                            break;
                        case 0:
                            return (array)[
                                "plagiarism_plagkh_apiurl" => $ex->getMessage()
                            ];
                            break;
                        default:
                            throw $ex;
                            break;
                    }
                } catch (plagiarism_plagkh_auth_exception $ex) {
                    return (array)[
                        "plagiarism_plagkh_secret" => get_string('clinvalidkeyorsecret', 'plagiarism_plagkh')
                    ];
                }
            }
        } else {
            if (!isset($newconfigsecret) || !isset($newconfigkey) || empty($newconfigkey) || empty($newconfigsecret)) {
                return (array)[
                    "plagiarism_plagkh_secret" => get_string('clinvalidkeyorsecret', 'plagiarism_plagkh')
                ];
            }
        }
        return array();
    }

    /**
     * Init the form data form both DB and plagkh API
     */
    public function init_form_data() {
        $cache = cache::make('core', 'config');
        $cache->delete('plagiarism_plagkh');

        // Get moodle admin config.
        $plagiarismsettings = (array) plagiarism_plagkh_pluginconfig::admin_config();

        if (
            !isset($plagiarismsettings['plagiarism_plagkh_apiurl']) ||
            empty($plagiarismsettings['plagiarism_plagkh_apiurl'])
        ) {
            $plagiarismsettings['plagiarism_plagkh_apiurl'] = plagiarism_plagkh_comms::plagkh_api_url();
        }

        $cldbdefaultconfig = plagiarism_plagkh_moduleconfig::get_modules_default_config();

        if (!isset($plagiarismsettings["plagiarism_plagkh_studentdisclosure"])) {
            $plagiarismsettings["plagiarism_plagkh_studentdisclosure"] =
                get_string('clstudentdisclosuredefault', 'plagiarism_plagkh');
        }

        $this->set_data($plagiarismsettings);
    }

    /**
     * Display the form to admins
     */
    public function display() {
        ob_start();
        parent::display();
        $form = ob_get_contents();
        ob_end_clean();
        return $form;
    }

    /**
     * Save form data
     * @param stdClass $data
     */
    public function save(stdClass $data) {
        global $CFG;

        // Save admin settings.
        $configproperties = plagiarism_plagkh_pluginconfig::admin_config_properties();
        foreach ($configproperties as $property) {
            plagiarism_plagkh_pluginconfig::set_admin_config($data, $property);
        }

        // Check if plugin is enabled.
        $plagiarismmodules = array_keys(core_component::get_plugin_list('mod'));
        $pluginenabled = 0;
        foreach ($plagiarismmodules as $module) {
            if (plugin_supports('mod', $module, FEATURE_PLAGIARISM)) {
                $property = "plagiarism_plagkh_mod_" . $module;
                $ismoduleenabled = (!empty($data->$property)) ? $data->$property : 0;
                if ($ismoduleenabled) {
                    $pluginenabled = 1;
                }
            }
        }

        // Set if plagkh plugin is enabled.
        set_config('enabled', $pluginenabled, 'plagiarism_plagkh');
        if ($CFG->branch < 39) {
            set_config('plagkh_use', $pluginenabled, 'plagiarism');
        }

        $cache = cache::make('core', 'config');
        $cache->delete('plagiarism_plagkh');
    }
}
