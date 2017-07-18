<?php

/**
 * This library is a third-party proposal for standardizing mail
 * message constitution for third party modules. It is actually used
 * by all ethnoinformatique.fr module. It relies on mail and message content
 * templates that should reside in a mail/{$lang} directory within the 
 * module space.
 */

defined('MOODLE_INTERNAL') || die;

/**
 * @package    tool_delivery
 * @category   tool
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * useful templating functions from an older project of mine, hacked for Moodle
 * @param template the template's file name from $CFG->sitedir
 * @param infomap a hash containing pairs of parm => data to replace in template
 * @return a fully resolved template where all data has been injected
 */
function tool_delivery_compile_mail_template($template, $infomap, $lang = '') {
    global $USER;
    
    if (empty($lang)) $lang = $USER->lang; 
    $lang = substr($lang, 0, 2); // be sure we are in moodle 2
    
    $notification = implode('', tool_delivery_get_mail_template($template, $lang));
    foreach($infomap as $aKey => $aValue){
        $notification = str_replace("<%%$aKey%%>", $aValue, $notification);
    }
    return $notification;
}

/**
 * resolves and get the content of a Mail template, acoording to the user's current language.
 * @param virtual the virtual mail template name
 * @param module the current module
 * @param lang if default language must be overriden
 * @return string the template's content or false if no template file is available
 */
function tool_delivery_get_mail_template($virtual, $lang = ''){
    global $CFG;

    if ($lang == '') {
        $lang = $CFG->lang;
    }
    $templateName = "{$CFG->dirroot}/admin/tool/delivery/mails/{$lang}/{$virtual}.tpl";
    if (file_exists($templateName))
        return file($templateName);

    debugging("template $templateName not found");
    return array();
}
