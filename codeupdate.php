<?php

include_once '../../../config.php';

$context = context_system::instance();

require_login();
require_capability('moodle/site:config', $context);

require_once('delivery_form.php');

$PAGE->set_context($context);

$baseurl = $CFG->wwwroot.'/admin/tool/delivery/codeupdate.php';

$debug = 1;

$mform = new Delivery_Form();

$deliverypathext = ($CFG->tooldirectdeliverytools) ? '' : $CFG->tooldeliverymethod.'_toolkit/' ;

$sudo = '';
if ($CFG->tooldeliverysudo){
	$sudo = ($CFG->tooldeliverysudouser) ? "sudo -u {$CFG->tooldeliverysudouser} " : 'sudo ';
}

$PAGE->set_url($baseurl);    
$PAGE->navbar->add(get_string('delivery', 'tool_delivery'));
$PAGE->set_heading($SITE->fullname);

if ($mform->is_cancelled()){
	if ($CFG->tooldeliveryenablesessions){
		redirect($CFG->wwwroot.'/admin/tool/delivery/index.php');
	}
} else {
	if ($data = $mform->get_data()){
		
		$workingpath = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext;
		chdir($workingpath);
		
		switch($data->cmd){
			case 'syncback':
				$args = escapeshellarg($data->synccomponentpath);
				$reportargs = $data->synccomponentpath;
				$deliverycmdfile = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext.'syncback';
				$deliverycmd = $sudo.$CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext."syncback $args";
				$reportcmd = 'syncback';
				break;
			case 'supersyncback':
				$args = escapeshellarg($data->synccomponentpath);
				$reportargs = $data->synccomponentpath;
				$deliverycmdfile = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext.'supersyncback';
				$deliverycmd = $sudo.$CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext."suprsyncback $args";
				$reportcmd = 'supersyncback';
				break;
			case 'goback':				
				$deliverycmdfile = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext.'goback';
				$reportargs = '';
				$deliverycmd = $sudo.$deliverycmdfile;
				$reportcmd = 'goback';
				break;
			case 'update':
				$args = escapeshellarg($data->componentpath);
				$reportargs = $data->componentpath;
				$deliverycmdfile = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext.'update';
				$deliverycmd = $sudo.$CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext."update $args";
				$reportcmd = 'update';
				break;
			case 'backtoprod':				
				$deliverycmdfile = $CFG->tooldeliveryprodscriptpath.'/'.$deliverypathext.$CFG->tooldeliverymethod.'toprod';
				$reportargs = '';
				$deliverycmd = $sudo.$deliverycmdfile;
				$reportcmd = $CFG->tooldeliverymethod.'toprod';
				break;
		}		
		if (!is_file($deliverycmdfile)){
			print_error('errornotfound', 'tool_delivery', '', $deliverycmdfile);
		}
		
		if (!is_executable($deliverycmdfile) && !$debug && !$CFG->tooldeliverysudouser){
			print_error('errornotexecutable', 'tool_delivery', '', $deliverycmdfile);
		}

		if ($CFG->tooldeliveryenablesessions){

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
	
		chdir($CFG->tooldeliveryprodscriptpath);
		$result = $OUTPUT->box_start('generalbox', '', true);
		$result .= '<pre>';
		$result .= "Executing: $deliverycmd <br/>";
		exec($deliverycmd, $output, $return);
		sleep(3); // let the operation perform in stable
		if ($return == 126){
			$result .= "Result: <span style=\"color:#D00000\">Could not execute \"$deliverycmd\". Permission problem</span><br/>";
		} elseif ($return == 127){
			$result .= "Result: <span style=\"color:#D00000\">Could not execute \"$deliverycmd\". Command not found.</span><br/>";
		} else {
			$result .= "Result: $return<br/>";
			// on success, clear the APC cache for avoiding cache misloads
			if (function_exists('apc_clear_cache')){
				apc_clear_cache('opcode');
			}
		}
		$result .= implode('<br/>', $output);
		$result .= '</pre>';
		$result .= $OUTPUT->box_end(true);		
	
		// redirect to run the result page on stable codebase 
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

if (is_dir($CFG->tooldeliveryprodscriptpath.'/'.$CFG->tooldeliverydir.'-'.strtoupper($CFG->tooldeliverymethod))){
	echo $OUTPUT->box(get_string('deliverystate', 'tool_delivery'), 'delivery deliverystate'); 
} else {
	echo $OUTPUT->box(get_string('productionstate', 'tool_delivery'), 'production deliverystate'); 
}

if($CFG->localdeliverysudo){
	$OUTPUT->box(get_string('sudomodeon','tool_delivery'), 'sudosignal'); 
}

// need refresh till situation may have changed in the meanwhile
$mform = new Delivery_Form();

$mform->display();

echo $OUTPUT->footer();
