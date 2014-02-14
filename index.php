<?php

include '../../../config.php';

$systemcontext = context_system::instance();
$url = $CFG->wwwroot.'/admin/tool/delivery/index.php';

$action = optional_param('what', '', PARAM_TEXT);
$sessionid = optional_param('sessionid', '', PARAM_TEXT);

require_login();
require_capability('moodle/site:config', $systemcontext);

$fs = get_file_storage();

$lastsession = $DB->get_record('tool_delivery_session', array(), 'id,MAX(timecreated)');
if ($action == 'clear'){
	// deleting reports and reopen if last session
	$fs->delete_area_files($systemcontext->id, 'tool_delivery', 'reports', $sessionid);
	$session = $DB->get_record('tool_delivery_session', array('id' => $sessionid));
	if ($session->id == $lastsession->id){
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

if ($sessions){
	foreach($sessions as $s){
		// capturates last session
		if (!$l){
			 $last = $s;
			 $l = true;
		}

		// get produced reports
		$reports = $fs->get_area_files($systemcontext->id, 'tool_delivery', 'reports', $s->id);
		$reportstring = '';
		foreach($reports as $r){
			$reporturl = moodle_url::make_pluginfile_url($systemcontext->id, 'tool_delivery', 'reports', $s->id, $r->get_filepath(), $r->get_filename());
			$reportstring .= html_writer::link($reporturl, $r->get_filename()).'<br/>';
		}
		
		$cleararealink = '';
		if ($lastsession && ($s->id == $lastsession->id)){
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
