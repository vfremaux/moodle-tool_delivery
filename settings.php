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

defined('MOODLE_INTERNAL') || die;

/**
 * @package    tool_delivery
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($hassiteconfig) {

    if (!isset($CFG->localdeliverymethod)) set_config('localdeliverymethod', 'svn');
    if (!isset($CFG->localdeliveryprodscriptpath)) set_config('localdeliverymethod', @$CFG->dirroot.'/local/delivery/prodscripts');
    if (!isset($CFG->localdeliverydir)) set_config('localdeliverymethod', '');
    if (!isset($CFG->localdeliverysudo)) set_config('localdeliverysudo', 0);
    if (!isset($CFG->localdeliverysudouser)) set_config('localdeliverysudouser', '');

    // settings

    $temp = new admin_settingpage('delivery', get_string('delivery', 'tool_delivery'));

    if (empty($CFG->tooldeliveryenablesessions)){
        $temp->add(new admin_setting_heading('codeupdate', get_string('deliverytools', 'tool_delivery'), "<a href=\"{$CFG->wwwroot}/admin/tool/delivery/codeupdate.php\">".get_string('accesstools', 'tool_delivery').'</a>'));
    } else {
        $temp->add(new admin_setting_heading('codeupdate', get_string('deliverytools', 'tool_delivery'), "<a href=\"{$CFG->wwwroot}/admin/tool/delivery/index.php\">".get_string('accesstools', 'tool_delivery').'</a>'));
    }

    $temp->add(new admin_setting_heading('codeupdateoptions', get_string('deliveryoptions', 'tool_delivery'), ''));

    $key = 'tool_delivery/method';
    $label = get_string('tooldeliverymethod', 'tool_delivery');
    $desc = get_string('configtooldeliverymethod', 'tool_delivery');
    $default = 'svn';
    $methodoptions = [
        'cvs' => get_string('cvs', 'tool_delivery'),
        'svn' => get_string('svn', 'tool_delivery'),
    ];
    $temp->add(new admin_setting_configselect($key, $label, $desc, $default, $methodoptions));

    $key = 'tool_delivery/prodscriptpath';
    $label = get_string('tooldeliveryprodscriptpath', 'tool_delivery');
    $desc = get_string('configtooldeliveryprodscriptpath', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtext($key, $label, $desc, $default));

    $key = 'tool_delivery/dir';
    $label = get_string('tooldeliverydir', 'tool_delivery');
    $desc = get_string('configtooldeliveryprodscriptpath', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtext($key, $label, $desc, $default));

    $key = 'tool_delivery/directtools';
    $label = get_string('tooldirectdeliverytools', 'tool_delivery');
    $desc = get_string('configtooldirectdeliverytools', 'tool_delivery');
    $temp->add(new admin_setting_configcheckbox($key, $label, $desc, 0));

    $temp->add(new admin_setting_heading('security', get_string('security', 'tool_delivery'), ''));

    $key = 'tool_delivery/sudo';
    $label = get_string('tooldeliverysudo', 'tool_delivery');
    $desc = get_string('configtooldeliverysudo', 'tool_delivery');
    $default = 0;
    $temp->add(new admin_setting_configcheckbox($key, $label, $desc, $default));

    $key = 'tool_delivery/sudouser';
    $label = get_string('tooldeliverysudouser', 'tool_delivery');
    $desc = get_string('configtooldeliverysudouser', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtext($key, $label, $desc, $default));

    $key = 'tool_delivery/enablesessions';
    $label = get_string('tooldeliveryenablesessions', 'tool_delivery');
    $desc = get_string('configtooldeliveryenablesessions', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configcheckbox($key, $label, $desc, $default));

    $key = 'tool_delivery/sessionopenrecipients';
    $label = get_string('tooldeliverysessionopenrecipients', 'tool_delivery');
    $desc = get_string('configtooldeliverysessionopenrecipients', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtextarea($key, $label, $desc, $default));

    global $CFG;
    require_once($CFG->dirroot.'/admin/tool/delivery/adminlib.php');
    // $temp->add(new admin_setting_configimage('tool_delivery/reportlogo', get_string('tooldeliveryreportlogo', 'tool_delivery'), get_string('configtooldeliveryreportlogo', 'tool_delivery'), 'tool_delivery'));

    $temp->add(new admin_setting_heading('envswitch', get_string('environmentswitch', 'tool_delivery'), ''));

    $key = 'tool_delivery/prodtostaging';
    $label = get_string('configprodtostaging', 'tool_delivery');
    $desc = get_string('configprodtostaging_desc', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtextarea($key, $label, $desc, $default));

    $key = 'tool_delivery/stagingtoprod';
    $label = get_string('configstagingtoprod', 'tool_delivery');
    $desc = get_string('configstagingtoprod_desc', 'tool_delivery');
    $default = '';
    $temp->add(new admin_setting_configtextarea($key, $label, $desc, $default));

    $ADMIN->add('development', $temp);
}