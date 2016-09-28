<?php
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
require_once($CFG->dirroot.'/admin/tool/delivery/delivery_session_form.php');
require_once($CFG->dirroot.'/admin/tool/delivery/lib.php');

$sessionid = optional_param('sessionid', 0, PARAM_INT);
$action = optional_param('what', '', PARAM_ALPHA); // open, update or close

$systemcontext = context_system::instance();
$url = new moodle_url('/admin/tool/delivery/session.php');

// Security.

require_login();
require_capability('moodle/site:config', $systemcontext);

$PAGE->set_context($systemcontext);
$PAGE->set_url($url);
$PAGE->navbar->add(get_string('pluginname', 'tool_delivery'));
$PAGE->navbar->add(get_string('deliverysessions', 'tool_delivery'));
$PAGE->set_heading(get_string('deliverytools', 'tool_delivery'));
$PAGE->set_title(get_string('deliverytools', 'tool_delivery'));

$form = new Delivery_Session_Form($url.'?sessionid='.$sessionid, array('mode' => $action));

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/tool/delivery/index.php'));
}

if ($sessionid) {
    $session = $DB->get_record('tool_delivery_session', array('id' => $sessionid));
}

if (optional_param('savetrack', '', PARAM_TEXT)){
    $paramkeys = preg_grep('/comment_\d+/', array_keys($_POST));
    if (!empty($paramkeys)){
        foreach($paramkeys as $k){
            $logid = str_replace('comment_', '', $k);
            $DB->set_field('tool_delivery_session_log', 'comment', $_POST[$k], array('id' => $logid));
        }
    }
    $what = 'update';
} else {
    if ($data = $form->get_data()) {
        if ($sessionid) {
            $data->id = $sessionid;
            $data->timemodified = time();

            // first save
            $DB->update_record('tool_delivery_session', $data);

            if (!empty($data->saveandclose)) {
                tool_delivery_close_session($session);
                // close it actually if all goes ok	
                $data->timeclosed = time();
            }

            // first save
            $DB->update_record('tool_delivery_session', $data);

        } else {
            $data->timecreated = time();
            $data->timemodified = 0;
            $data->timeclosed = 0;
            $data->userid = $USER->id;
            $DB->insert_record('tool_delivery_session', $data);
            tool_delivery_open_session($data);
        }

        if(debugging()){
            echo $OUTPUT->header();
            echo $OUTPUT->continue_button(new moodle_url('/admin/tool/delivery/index.php'));
            echo $OUTPUT->footer();
            exit;
        }
        redirect(moodle_url('/admin/tool/delivery/index.php'));
    }
}

if (!empty($session)) {
    $form->set_data($session);
}

echo $OUTPUT->header();
$form->display();

if (!empty($session)) {
    if ($sessiontrack = $DB->get_records('tool_delivery_session_log', array('sessionid' => $session->id), 'timeupdated DESC')){
        echo '<form name="sessiontrack" action="'.$url.'" method="POST">';
        echo '<input type="hidden" name="sessionid" value="'.$sessionid.'" />';
        echo '<input type="hidden" name="what" value="'.$action.'" />';

        $timestr = get_string('time', 'tool_delivery');
        $actionstr = get_string('action', 'tool_delivery');
        $componentstr = get_string('component', 'tool_delivery');
        $commentstr = get_string('comment', 'tool_delivery');

        $table = new html_table();
        $table->head = array("<b>$timestr</b>", "<b>$actionstr</b>", "<b>$componentstr</b>", "<b>$commentstr</b<");
        $table->size = array('15%', '15%', '20%', '40%');
        $table->align = array('left', 'left', 'left', 'left');
        foreach ($sessiontrack as $st) {
            $data = array();
            $data[] = strftime('%Y-%a-%d %H:%I-%S', $st->timeupdated);
            $data[] = $st->command;
            $data[] = $st->component;
            $data[] = '<textarea name="comment_'.$st->id.'" cols="30" rows="4">'.$st->comment.'</textarea>';
            $table->data[] = $data;
        }

        echo html_writer::table($table);
        echo '<p><center><input type="submit" name="savetrack" value="'.get_string('save', 'tool_delivery').'" /></center></p>';
        echo '</form>';
    }
}

echo $OUTPUT->footer();
