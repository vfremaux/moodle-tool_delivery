<?php

require_once $CFG->dirroot.'/admin/tool/delivery/pdfgenerate.php';
require_once $CFG->dirroot.'/admin/tool/delivery/mailtemplatelib.php';

/**
* notify stakeholders
*
*/
function tool_delivery_open_session($session){
	global $DB, $CFG, $USER, $SITE;

	$posttext = tool_delivery_compile_mail_template('deliverySessionOpen', array('SITE' => $SITE->fullname, 'DESCR' => $session->description), $USER->lang);
	$posthtml = tool_delivery_compile_mail_template('deliverySessionOpenHtml', array('SITE' => $SITE->fullname, 'DESCR' => $session->description), $USER->lang);

	if (!empty($CFG->tooldeliverysessionopenrecipients)){
		$recipients = explode(',', $CFG->tooldeliverysessionopenrecipients);
		if (!empty($recipients)){
			foreach($recipients as $r){
			    $destination = new stdClass;
			    $destination->email = $r;
			    $postsubject = get_string('sessionopening', 'tool_delivery', $SITE->shortname);
				@email_to_user($destination, $USER, $postsubject, $posttext, $posthtml);
			}
		}
	}
	
	ini_set('apc.enabled', 0);
}

/**
* make a PDF document for delivery report
* send the document among with notification
*
*/
function tool_delivery_close_session($session){
	global $DB, $CFG, $USER, $SITE;

	// produce PDF report
	$sessionlog = $DB->get_records('tool_delivery_session_log', array('sessionid' => $session->id));
	if ($sessionlog){
		$pdfstring = generate_pdf($session, $sessionlog);
	} else {
		return;
	}
	
	$systemcontext = context_system::instance();

	// add new report to file storage	
	$fs = get_file_storage();
	$filerec = new StdClass;
	$filerec->contextid = $systemcontext->id;
	$filerec->component = 'tool_delivery';
	$filerec->filearea = 'reports';
	$filerec->itemid = $session->id;
	$filerec->filepath = '/';
	$filerec->filename = 'delivery_report_'.strftime('%Y%m%d_%H%I%S').'.pdf';
	$storedfile = $fs->create_file_from_string($filerec, $pdfstring);
	
	// get report physical location for attachment
	$contenthash = $storedfile->get_contenthash();
	$pathhash = tool_delivery_get_path_from_hash($contenthash);
	$attachment = '/filedir/'.$pathhash.'/'.$contenthash;
	if (empty($attachment)){
		echo 'No attachment as '.$pathhash.'/'.$contenthash;
	}
		
	// prepare email
	$posttext = tool_delivery_compile_mail_template('deliverySessionClose', array('SITE' => $SITE->fullname), $USER->lang);
	$posthtml = tool_delivery_compile_mail_template('deliverySessionCloseHtml', array('SITE' => $SITE->fullname), $USER->lang);
	$postsubject = get_string('newdeliverysession', 'tool_delivery');

	if (!empty($CFG->tooldeliverysessionopenrecipients)){
		$recipients = explode(',', $CFG->tooldeliverysessionopenrecipients);
		foreach($recipients as $r){
		    $destination = new stdClass;
		    $destination->email = $r;
		    $postsubject = get_string('sessionclosing', 'tool_delivery', $SITE->shortname);
			@email_to_user($destination, $USER, $postsubject, $posttext, $posthtml, $attachment, get_string('deliveryreport', 'tool_delivery').'.pdf');
		}
	}	

	ini_set('apc.enabled', 1);
}

/**
* this function publicizes retireval of the physical path to 
* a file stored for mail attachement or for PDF generation.
*/
function tool_delivery_get_path_from_hash($contenthash){
	global $CFG;
	
	$l1 = $contenthash[0].$contenthash[1];
    $l2 = $contenthash[2].$contenthash[3];
    return "$l1/$l2";
}

/**
* Sends the files for sharedresources
*
*/
function tool_delivery_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;

	// strong security to admins.
    require_login();
    $systemcontext = context_system::instance();
    require_capability('moodle/site:config', $systemcontext);

    $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
    
    $itemid = array_shift($args);
    
    if ($filearea === 'reports') {
        $relativepath = implode('/', $args);
        $fullpath = "/{$systemcontext->id}/tool_delivery/reports/{$itemid}/{$relativepath}";
        $lifetime = 0; // no caching here

    } else {
        return false;
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // finally send the file
    send_stored_file($file, $lifetime, 0, false);
}
