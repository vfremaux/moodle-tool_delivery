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

defined('MOODLE_INTERNAL') || die;

/**
 * @package    tool_delivery
 * @category   tool
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * A4_embedded delivery report
 */
require_once($CFG->dirroot.'/local/vflibs/tcpdf/tcpdf.php');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

function generate_pdf($session, $sessionlog = array()){
    global $USER, $SITE, $CFG;

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    $pdf->SetTitle(get_string('reporttitle', 'tool_delivery'));
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(false, 0);
    $pdf->AddPage();

    // Define variables
    // Portrait
    $x = 20;
    $y = 70;
    $lineincr = 8;
    $dblelineincr = 16;
    $smalllineincr = 5;

    // Set alpha to no-transparency
    // $pdf->SetAlpha(1);
    
    // Add images and lines
    tool_delivery_print_logo($pdf);

    // Add images and lines
    tool_delivery_draw_frame($pdf);
    
    // Make report
    $pdf->SetTextColor(0, 0, 120);
    $title = get_string('deliveryreport', 'tool_delivery');
    $y = tool_delivery_print_text($pdf, $title, $x + 40, 60, '', '', 'C', 'freesans', '', 20);

    $pdf->SetTextColor(0);

    $y += $dblelineincr;
    $label = get_string('application', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 13);
    $y = tool_delivery_print_text($pdf, $SITE->fullname, $x + 50, $y, '', '', 'L', 'freesans', '', 13);

    $y += $dblelineincr;
    $label = get_string('user').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, fullname($USER), $x + 50, $y, '', '', 'L', 'freesans', '', 10);

    $y += $lineincr;
    $label = get_string('sessionstart', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, strftime("%Y-%m-%d %H:%I", $session->timecreated), $x + 50, $y, '', '', 'L', 'freesans', '', 10);

    $y += $lineincr;
    $label = get_string('sessionend', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, strftime("%Y-%m-%d %H:%I", $session->timeclosed), $x + 50, $y, '', '', 'L', 'freesans', '', 10);

    $y += $lineincr;
    $label = get_string('summary', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, $session->description, $x + 50, $y, 110, '', 'L', 'freesans', '', 9);

    $y += $lineincr;
    $label = get_string('impact', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, $session->impact, $x + 50, $y, 110, '', 'L', 'freesans', '', 9);

    $y += $lineincr;
    $y = tool_delivery_check_page_break($pdf, $y);            
    $label = get_string('outputstatus', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, $session->outputstate, $x + 50, $y, 110, '', 'L', 'freesans', '', 9);

    $y += $lineincr;
    $y = tool_delivery_check_page_break($pdf, $y);            
    $label = get_string('followup', 'tool_delivery').':';
    tool_delivery_print_text($pdf, $label, $x, $y, '', '', 'L', 'freesans', '', 10);
    $y = tool_delivery_print_text($pdf, $session->followup, $x + 50, $y, 110, '', 'L', 'freesans', '', 9);

    $color = 0;

    $pdf->startTransaction();
    // create outer line border in selected color
    $y += $dblelineincr;
    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(200);
    $pdf->Line($x - 10, $y, $x + 157, $y);
    if ($y > 250){
        $pdf->rollbackTransaction(true);
    } else {
        $pdf->commitTransaction();
    }

    $y = tool_delivery_check_page_break($pdf, $y);

    $i = 0;
    $y += $lineincr;
    if (!empty($sessionlog)) {
        foreach($sessionlog as $log){
            $y = tool_delivery_print_log($pdf, $x, $y, 'L', 'freesans', '', 9, $log);

            $y = tool_delivery_check_page_break($pdf, $y);
            
            $i++;
        }
    }
    
    $return = $pdf->Output('', 'S');
    return $return;
}

function tool_delivery_check_page_break(&$pdf, $y){

    if ($y > 250) {
        $pdf->addPage('P', 'A4', true);
        $y = 70;
    
        // Add images and lines
        tool_delivery_print_logo($pdf);
    
        // Add images and lines
        tool_delivery_draw_frame($pdf);
    }

    return $y;
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function tool_delivery_print_text(&$pdf, $text, $x, $y, $l = '', $h = '', $align = 'L', $font='freeserif', $style = '', $size=10) {

    $pdf->setFont($font, $style, $size);
    $pdf->writeHTMLCell($l, $h, $x, $y, $text, 0, 1, 0, true, $align);

    return $pdf->getY();
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 * @return the new Y pos after the log line has been written
 */
function tool_delivery_print_log(&$pdf, $x, $y, $align, $font='freeserif', $style, $size = 10, $log) {

    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);

    $date = strftime('%Y-%m-%d %H:%I', $log->timeupdated);
    $pdf->writeHTMLCell(30, 0, $x, $y, $date, 0, 0, 0, true, $align);

    $x += 30;
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(25, 0, $x, $y, $log->command, 0, 0, 0, true, $align);

    $x += 25;
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(50, 0, $x, $y, $log->component, 0, 0, 0, true, $align);

    /*
    $comment = wordwrap($log->comment, 35);
    $lines = substr_count($comment, "\n");
    */
    $x += 50;
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(70, 0, $x, $y, $log->comment, 0, 1, 0, true, $align);

    return $pdf->getY();
}

/**
 * Creates rectangles for line border for A4 size paper.
 *
 * @param stdClass $pdf
 */
function tool_delivery_draw_frame(&$pdf) {

    // create outer line border in selected color
    $pdf->SetLineWidth(0.5);
    $pdf->SetDrawColor(200);
    $pdf->Rect(10, 10, 190, 277);
}

/**
 * Prints logo image from the borders folder in PNG or JPG formats.
 *
 * @param stdClass $pdf;
 */
function tool_delivery_print_logo(&$pdf) {
    global $CFG;

    $fs = get_file_storage();
    $systemcontext = context_system::instance();

    $files = $fs->get_area_files($systemcontext->id, 'tool_delivery', 'tooldeliveryreportlogo', 0);

    if (!empty($files)){
        $logofile = array_pop($files);
    } else {
        return;
    }

    $contenthash = $logofile->get_contenthash();
    $pathhash = tool_delivery_get_path_from_hash($contenthash);
    $realpath = $CFG->dataroot.'/filedir/'.$pathhash.'/'.$contenthash;

    $size = getimagesize($realpath);

    // converts 72 dpi images into mm
    $pdf->Image($realpath, 20, 20, $size[0] / 2.84, $size[1] / 2.84);
}

