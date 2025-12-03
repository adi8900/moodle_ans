<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_nextblocks.
 *
 * @package     mod_nextblocks
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT, $PAGE, $CFG, $page, $USER;

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

$context = context_module::instance($cm->id);
require_capability('mod/nextblocks:view', $context);

echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">';

$blocklyPath = new moodle_url('/mod/nextblocks/blockly/');
echo '
<script src="' . $blocklyPath->out() . 'blockly_compressed.js"></script>
<script src="' . $blocklyPath->out() . 'blocks_compressed.js"></script>
<script src="' . $blocklyPath->out() . 'msg/en.js"></script>
<script src="' . $blocklyPath->out() . 'javascript_compressed.js"></script>
<script src="' . $blocklyPath->out() . 'python_compressed.js"></script>';

$cmid = $PAGE->cm->id;
$cm = get_coursemodule_from_id('nextblocks', $cmid, 0, false, MUST_EXIST);
$instanceid = $cm->instance;

// Call init, with saved workspace and tests file if they exist.
$record = $DB->get_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance]);
$savedworkspace = $record ? $record->saved_workspace : null;

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

$modulecontext = context_module::instance($cm->id);

$existingjson = $fs->get_file(
    $modulecontext->id,
    'mod_nextblocks',
    'attachment',
    $cm->instance,
    '/',
    'nextblockstests'.$cm->instance.'.json'
);

if (!$existingjson) {
    nextblocks_convert_tests_file_to_json($instanceid);
}

// The contextid might not be accurate, so we search based on instanceid instead.
$records = $DB->get_records_sql("
    SELECT contextid, filepath, filename
    FROM {files}
    WHERE component = :component
      AND filearea  = :filearea
      AND itemid    = :itemid
      AND filename != '.'",
    [
        'component' => 'mod_nextblocks',
        'filearea'  => 'attachment',
        'itemid'    => $instanceid,
        'filename'  => 'nextblockstests'.$instanceid.'.json',
    ]
);

$rec = reset($records);

$fs = get_file_storage();
$testsfile = $fs->get_file(
    $rec->contextid,
    'mod_nextblocks',
    'attachment',
    $instanceid,
    $rec->filepath,
    $rec->filename
);

$testsfilecontents = $testsfile ? $testsfile->get_content() : null;

if ($record) {
    $remainingsubmissions = $moduleinstance->maxsubmissions - $record->submissionnumber;
} else {
    $remainingsubmissions = $moduleinstance->maxsubmissions;
}

$limits = $DB->get_records_sql_menu(
    "SELECT blocktype, blocklimit
     FROM {nextblocks_blocklimit}
     WHERE nextblocksid = :instanceid
     AND blocklimit != -1",  // Exclude infinite limits
    ['instanceid' => $instanceid]
);

$reactions = [intval($moduleinstance->reactionseasy), intval($moduleinstance->reactionsmedium), 
    intval($moduleinstance->reactionshard)];
$lastuserreaction = $record ? intval($record->reacted) : 0;

$user = $DB->get_record('user', ['id' => $USER->id]);
$username = $user->firstname . ' ' . $user->lastname;
$PAGE->requires->js_call_amd('mod_nextblocks/codeenv', 'init', [$testsfilecontents, $savedworkspace, $customblocksjson,
    $remainingsubmissions, $reactions, $lastuserreaction, 0, $username, $cmid, $limits]);
$PAGE->requires->js_call_amd('mod_nextblocks/chat', 'init', [$cmid, $instanceid, $username]);

$PAGE->set_url('/mod/nextblocks/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

echo $OUTPUT->header();

$title = $DB->get_field('nextblocks', 'name', ['id' => $instanceid]);
$description = $DB->get_field('nextblocks', 'intro', ['id' => $instanceid]);

$runtestsbutton = $testsfile ? '<input id="runTestsButton" type="submit" class="btn btn-primary m-2" value="'
    .get_string("nextblocks_runtests", "nextblocks").'" />' : '';


$data = [
    'title' => $OUTPUT->heading($title),
    'description' => $description,
    'outputHeading' => $OUTPUT->heading("Output", $level = 4),
    'reactionsHeading' => $OUTPUT->heading("Reactions", $level = 4),
    'runTestsButton' => $runtestsbutton,
    'showSubmitButton' => true,
    'showGrader' => false,
];

echo $OUTPUT->render_from_template('mod_nextblocks/nextblocks', $data);

echo $OUTPUT->footer();
