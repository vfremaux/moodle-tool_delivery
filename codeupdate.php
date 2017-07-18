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
 * @package    tool_delivery
 * @category   tool
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');

$context = context_system::instance();

// Security.

require_login();
require_capability('moodle/site:config', $context);

require_once('delivery_form.php');

$PAGE->set_context($context);

$baseurl = new moodle_url('/admin/tool/delivery/codeupdate.php');
$config = get_config('tool_delivery');

$mform = new Delivery_Form();

$deliverypathext = ($config->directtools) ? '' : $config->method.'_toolkit/';

$sudo = '';
if ($config->sudo) {
    $sudo = ($config->sudouser) ? "sudo -u {$config->sudouser} " : 'sudo ';
}

$PAGE->set_url($baseurl);
$PAGE->navbar->add(get_string('delivery', 'tool_delivery'));
$PAGE->set_heading($SITE->fullname);

if ($mform->is_cancelled()) {
    if ($config->enablesessions) {
        redirect(new moodle_url('/admin/tool/delivery/index.php'));
    }
} else {
    if ($data = $mform->get_data()) {

        $workingpath = $config->prodscriptpath.'/'.$deliverypathext;
        chdir($workingpath);

        switch ($data->cmd) {
            case 'syncback':
                if (!empty($data->synccomponentpath)) {
                    $args = escapeshellarg($data->synccomponentpath);
                } else {
                    $args = '';
                }
                $reportargs = $data->synccomponentpath;
                $deliverycmdfile = $config->prodscriptpath.'/'.$deliverypathext.'syncback';
                $deliverycmd = $sudo.$config->prodscriptpath.'/'.$deliverypathext."syncback $args";
                $reportcmd = 'syncback';
                break;

            case 'supersyncback':
                if (!empty($data->synccomponentpath)) {
                    $args = escapeshellarg($data->synccomponentpath);
                } else {
                    $args = '';
                }
                $reportargs = $data->synccomponentpath;
                $deliverycmdfile = $config->prodscriptpath.'/'.$deliverypathext.'supersyncback';
                $deliverycmd = $sudo.$config->prodscriptpath.'/'.$deliverypathext."suprsyncback $args";
                $reportcmd = 'supersyncback';
                break;

            case 'goback':
                $deliverycmdfile = $config->prodscriptpath.'/'.$deliverypathext.'goback';
                $reportargs = '';
                $deliverycmd = $sudo.$deliverycmdfile;
                $reportcmd = 'goback';
                break;

            case 'update':
                if (!empty($data->componentpath)) {
                    $args = escapeshellarg($data->componentpath);
                } else {
                    $args = '';
                }
                $reportargs = $data->componentpath;
                $deliverycmdfile = $config->prodscriptpath.'/'.$deliverypathext.'update';
                $deliverycmd = $sudo.$config->prodscriptpath.'/'.$deliverypathext."update $args";
                $reportcmd = 'update';
                break;

            case 'backtoprod':
                $deliverycmdfile = $config->prodscriptpath.'/'.$deliverypathext.$config->method.'toprod';
                $reportargs = '';
                $deliverycmd = $sudo.$deliverycmdfile;
                $reportcmd = $config->method.'toprod';
                break;
        }

        if (!is_file($deliverycmdfile)) {
            print_error('errornotfound', 'tool_delivery', '', $deliverycmdfile);
        }

        if (!is_executable($deliverycmdfile) && !$debug && !$config->sudouser) {
            print_error('errornotexecutable', 'tool_delivery', '', $deliverycmdfile);
        }

        if ($config->enablesessions) {

            $sql = "
                SELECT
                    MAX(id) AS id
                FROM
                    {tool_delivery_session}
                WHERE
                    timeclosed =  0
            ";

            $lastsession = $DB->get_record_sql($sql);
            $logrec = new StdClass();
            $logrec->sessionid = $lastsession->id;
            $logrec->command = $reportcmd;
            $logrec->component = $reportargs;
            $logrec->comment = @$data->comment;
            $logrec->timeupdated = time();
            $DB->insert_record('tool_delivery_session_log', $logrec);
        }

        chdir($config->prodscriptpath);
        $result = $OUTPUT->box_start('generalbox');
        $result .= '<pre>';
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache('opcode');
        }
        sleep(2); // let the operation perform in stable
        $result .= "Executing: $deliverycmd <br/>";
        exec($deliverycmd, $output, $return);
        sleep(6); // let the operation perform in stable
        if ($return == 126) {
            $result .= "Result: <span style=\"color:#D00000\">Could not execute \"$deliverycmd\". Permission problem</span><br/>";
        } elseif ($return == 127) {
            $result .= "Result: <span style=\"color:#D00000\">Could not execute \"$deliverycmd\". Command not found.</span><br/>";
        } else {
            $result .= "Result: $return<br/>";
            // on success, clear the APC cache for avoiding cache misloads
            if (function_exists('apc_clear_cache')) {
                apc_clear_cache('opcode');
            }
        }

        $result .= implode('<br/>', $output);
        $result .= '</pre>';
        $result .= $OUTPUT->box_end(true);

        // Redirect to run the result page on stable codebase.
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox');
        echo $result;
        unset($result);
        echo $OUTPUT->box_end();
        echo $OUTPUT->continue_button($baseurl);
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();

if (is_dir($config->prodscriptpath.'/'.$config->dir.'-'.strtoupper($config->method))) {
    echo $OUTPUT->box(get_string('deliverystate', 'tool_delivery'), 'delivery deliverystate'); 
} else {
    echo $OUTPUT->box(get_string('productionstate', 'tool_delivery'), 'production deliverystate'); 
}

if ($config->sudo) {
    $OUTPUT->box(get_string('sudomodeon','tool_delivery'), 'sudosignal'); 
}

// Need refresh till situation may have changed in the meanwhile.
$mform = new Delivery_Form();

$mform->display();

echo $OUTPUT->footer();
