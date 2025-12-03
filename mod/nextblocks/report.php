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
 * NextBlocks report page.
 *
 * @package    mod_nextblocks
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $PAGE, $OUTPUT, $USER, $DB;

use mod_nextblocks\form\grade_submit;

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$n = optional_param('n', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('nextblocks', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('nextblocks', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('nextblocks', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('nextblocks', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

// Import icons.
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">';

// Import blockly.
echo '<script src="./blockly/blockly_compressed.js"></script>
    <script src="./blockly/blocks_compressed.js"></script>
    <script src="./blockly/msg/en.js"></script>
    <script src="./blockly/javascript_compressed.js"></script>
    <script src="./blockly/python_compressed.js"></script>';


$userid = required_param('userid', PARAM_INT);

$instanceid = $cm->instance;

$record = $DB->get_record('nextblocks_userdata', ['userid' => $userid, 'nextblocksid' => $instanceid]);

$savedworkspace = $record->saved_workspace;

// Get custom blocks.
$customblocks = $DB->get_records('nextblocks_customblocks', ['nextblocksid' => $instanceid]);
$customblocksjson = [];
foreach ($customblocks as $customblock) {
    $customblocksjson[] = [
        'definition' => $customblock->blockdefinition,
        'generator' => $customblock->blockgenerator,
        'pythongenerator' => $customblock->blockpythongenerator,
    ];
}

$fs = get_file_storage();
$filenamehash = nextblocks_get_filenamehash($instanceid);

$testsfile = $fs->get_file_by_hash($filenamehash);
$testsfilecontents = $testsfile ? $testsfile->get_content() : null;

$reactions = [intval($moduleinstance->reactionseasy), intval($moduleinstance->reactionsmedium), 
    intval($moduleinstance->reactionshard)];
$lastuserreaction = intval($record->reacted);

if (has_capability('mod/nextblocks:gradeitems', context_module::instance($cm->id))) {
    $reporttype = 1;
} else {
    $reporttype = 2;
}

$limits = $DB->get_records_menu(
    'nextblocks_blocklimit',
    ['nextblocksid' => $instanceid],
    '',
    'blocktype,blocklimit'
);

// We need the username of the user whose report we are viewing,
// ...and the username of the logged in user. they can be different,
// ...in the case when a teacher is viewing a student's report.
$user = $DB->get_record('user', ['id' => $userid]);
$reportsubjectusername = $user->firstname . ' ' . $user->lastname;

$loggedinusername = $USER->firstname . ' ' . $USER->lastname;

$PAGE->requires->js_call_amd('mod_nextblocks/codeenv', 'init', [$testsfilecontents, $savedworkspace, $customblocksjson, 1, 
    $reactions, $lastuserreaction, $reporttype, $loggedinusername, $id, $limits]);
$PAGE->requires->js_call_amd('mod_nextblocks/chat', 'init', [$id, $instanceid, $loggedinusername]);

$PAGE->set_url('/mod/nextblocks/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string("report", "nextblocks") . format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$title = $DB->get_field('nextblocks', 'name', ['id' => $instanceid]);
$description = $DB->get_field('nextblocks', 'intro', ['id' => $instanceid]);

$runtestsbutton = $testsfile ? '<input id="runTestsButton" type="submit" class="btn btn-primary m-2" value="'
    .get_string("nextblocks_runtests", "nextblocks").'" />' : '';

$mform = new grade_submit();

if ($data = $mform->get_data()) {

    $grades = new stdClass();
    $grades->userid = $userid;
    $grades->rawgrade = $data->newgrade; // Fetch new grade from form.

    nextblocks_grade_item_update($moduleinstance, $grades); // Update grade in gradebook.

    // Update grade in database.
    $record->grade = $data->newgrade;
    $DB->update_record('nextblocks_userdata', $record);

    redirect(new moodle_url($PAGE->url, ['id' => $id, 'userid' => $userid]), 'Grade Updated');
} else {
    $graderform = $mform->render();

    $student = $DB->get_record('user', ['id' => $userid]);

    $currentgrade = $record->grade;
    $maxgrade = $moduleinstance->grade;

    $showgrader = has_capability('mod/nextblocks:gradeitems', context_module::instance($cm->id));

    $data = [
        'title' => $OUTPUT->heading($title),
        'description' => $description,
        'outputHeading' => $OUTPUT->heading(get_string('testsoutput', 'mod_nextblocks'), $level = 4),
        'reactionsHeading' => $OUTPUT->heading(get_string('reactions', 'mod_nextblocks'), $level = 4),
        'runTestsButton' => $runtestsbutton,
        'showSubmitButton' => false,
        'showGrader' => $showgrader,
        'graderForm' => $graderform,
        'studentName' => $reportsubjectusername,
        'currentGrade' => $currentgrade,
        'maxGrade' => $maxgrade,
    ];

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('mod_nextblocks/nextblocks', $data);
    echo $OUTPUT->footer();
}
