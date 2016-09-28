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

$systemcontext = context_system::instance();
$url = new moodle_url('/admin/tool/delivery/index.php');

$action = optional_param('what', '', PARAM_TEXT);
$sessionid = optional_param('sessionid', '', PARAM_TEXT);

// Security.

require_login();
require_capability('moodle/site:config', $systemcontext);

$fs = get_file_storage();

$lastsession = $DB->get_record('tool_delivery_session', array(), 'id,MAX(timecreated)');
if ($action == 'clear') {
    // deleting reports and reopen if last session
    $fs->delete_area_files($systemcontext->id, 'tool_delivery', 'reports', $sessionid);
    $session = $DB->get_record('tool_delivery_session', array('id' => $sessionid));
    if ($session->id == $lastsession->id) {
        $DB->set_field('tool_delivery_session', 'timeclosed', 0, array('id' => $sessionid));
    }
}

$sql = "
    SELECT
        s.*,
        COUNT(sl.id) as logs
    FROM
        {tool_delivery_session} s
    LEFT JOIN
        {tool_delivery_session_log} sl
    ON
        s.id = sl.sessionid
    GROUP BY
        s.id
    ORDER BY
        s.timecreated DESC
    ";

$sessions = $DB->get_records_sql($sql, array());

$PAGE->set_context($systemcontext);
$PAGE->set_url($url);
$PAGE->set_heading(get_string('deliverytools', 'tool_delivery'));
$PAGE->set_title(get_string('deliverytools', 'tool_delivery'));
$PAGE->navbar->add(get_string('pluginname', 'tool_delivery'));
$PAGE->navbar->add(get_string('deliverysessions', 'tool_delivery'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('deliverysessions', 'tool_delivery'));

$table = new html_table();

$datestartstr = get_string('datestart', 'tool_delivery');
$dateendstr = get_string('dateend', 'tool_delivery');
$titlestr = get_string('sessiontitle', 'tool_delivery');
$logsstr = get_string('sessionlogs', 'tool_delivery');
$reportsstr = get_string('reports', 'tool_delivery');
$userstr = get_string('user');

$table->head = array("<b>$datestartstr</b>", "<b>$dateendstr</b>", "<b>$titlestr</b>", "<b>$logsstr</b>", "<b>$reportsstr</b>", "<b>$userstr</b>");
$table->size = array('10%', '10%', '60%', '10%', '10%');

$last = null;
$l = false;

if ($sessions) {
    foreach ($sessions as $s) {
        // capturates last session
        if (!$l) {
             $last = $s;
             $l = true;
        }

        // Get produced reports.
        $reports = $fs->get_area_files($systemcontext->id, 'tool_delivery', 'reports', $s->id);
        $reportstring = '';
        foreach ($reports as $r) {
            $reporturl = moodle_url::make_pluginfile_url($systemcontext->id, 'tool_delivery', 'reports', $s->id, $r->get_filepath(), $r->get_filename());
            $reportstring .= html_writer::link($reporturl, $r->get_filename()).'<br/>';
        }
        
        $cleararealink = '';
        if ($lastsession && ($s->id == $lastsession->id)) {
            $cleararealink = html_writer::link($url.'?sessionid='.$s->id.'&what=clear', get_string('clearreportarea', 'tool_delivery'));
        }
        
        $user = $DB->get_record('user', array('id' => $s->userid));
        $data = array();
        $data[] = strftime('%Y-%a-%d %H:%I:%S', $s->timecreated);
        $data[] = ($s->timeclosed) ? strftime('%Y-%a-%d %H:%I:%S', $s->timeclosed) :  '' ;
        $data[] = $s->title;
        $data[] = $s->logs;
        $data[] = ($reportstring) ? $reportstring.'<br/>'.$cleararealink : '' ;
        $data[] = fullname($user);

        $table->data[] = $data;
    }
    
    echo html_writer::table($table);
} else {
    echo $OUTPUT->box($OUTPUT->notification(get_string('nosessions', 'tool_delivery')));
}

echo '<p>';
if (is_null($last) || $last->timeclosed) {
    echo '<a href="session.php?what=open">'.get_string('opensession', 'tool_delivery').'</a>';
} else {
    echo '<a href="codeupdate.php">'.get_string('continuesession', 'tool_delivery').'</a>';
    echo '&nbsp;-&nbsp;<a href="session.php?what=close&sessionid='.$last->id.'">'.get_string('closesession', 'tool_delivery').'</a>';
}
echo '</p>';

echo $OUTPUT->footer();
