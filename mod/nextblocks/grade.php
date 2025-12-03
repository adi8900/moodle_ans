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
 *
 * @package     mod_nextblocks
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Handles visualization of student grades
 */

global $PAGE, $DB, $CFG, $USER;
require_once("../../config.php");

$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

$cm = get_coursemodule_from_id('nextblocks', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$lesson = $DB->get_record('nextblocks', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);

$PAGE->set_url('/mod/nextblocks/grade.php', ['id' => $cm->id]);
if (has_capability('mod/nextblocks:viewreports', context_module::instance($cm->id))) {
    if ($userid) { // If there is a userid, then we are viewing one student's report.
        redirect('report.php?id='.$cm->id.'&userid='.$userid);
    } else { // If there is no userid, then we are viewing the report for all students.
        redirect('overview.php?id='.$cm->id);
    }
} else {
    redirect('view.php?id='.$cm->id);
}
