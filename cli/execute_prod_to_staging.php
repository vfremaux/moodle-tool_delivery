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
 * @package     tool_delivery
 * @copyright   2016 Valery Fremaux <valery@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/lib/clilib.php'); // Cli only functions.

// Now get cli options.

list($options, $unrecognized) = cli_get_params(array('help' => false,
                                                     'host' => false,
                                                     'debug' => false),
                                               array('h' => 'help',
                                                     'H' => 'host',
                                                     'd' => 'debug')
                                               );

if ($unrecognized) {
    $unrecognized = implode("\n ", $unrecognized);
    cli_error($unrecognized. " is not a recognized option\n");
}

if ($options['help']) {
    $help = "
Executes the switching SQL sequence from tool_delivery configuration.

Options:
-h, --help            Print out this help
-H, --host            The virtual moodle to play for. Main moodle install if missing.
-d, --debug           Debug mode

Example:
sudo -uwww-data php admin/tool/delivery/cli/execute_prod_to_staging.php
";

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // Mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

if (!defined('MOODLE_INTERNAL')) {
    // If we are still in precheck, this means this is NOT a VMoodle install and full setup has already run.
    // Otherwise we only have a tiny config at this location, sso run full config again forcing playing host if required.
    require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php'); // Global moodle config file.
}
echo('Config check : playing for '.$CFG->wwwroot."\n");

$config = get_config('tool_delivery');

if (empty($config->prodtostaging)) {
    echo "No switching SQL.";
    exit(0);
}

$queries = preg_split('/\r?\n\s*\r?\n/', $config->prodtostaging);

foreach ($queries as $q) {

    if (empty($q) || !preg_match('/^(UPDATE|DELETE|INSERT|ALTER)/', $q)) {
        // Ignore all empty or unknonw verbs.
        if (!empty($options['debug'])) {
            echo "Ignoring : $q\n\n";
        }
        continue;
    }

    // Force an ending semi-column to cloturate query.
    try {
        echo "\"$q\"\n";
        echo "\"".html_entities($q)."\"\n";
        $q = preg_replace('/;?\s*$/s', '', $q);
        echo "Filtered \"$q\"\n";
        if (!empty($options['debug'])) {
            echo "Executing : $q\n\n";
        }
        $DB->execute($q);
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}
echo "\n";
echo "Done.\n";