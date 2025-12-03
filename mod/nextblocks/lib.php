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
 * Library of interface functions and constants.
 *
 * @package     mod_nextblocks
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 *
 * @return true | null True if the feature is supported, null otherwise.
 */
function nextblocks_supports(string $feature): ?bool {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_MOD_INTRO:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_nextblocks into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object                       $moduleinstance An object from the form.
 * @param mod_nextblocks_mod_form|null $mform          The form.
 *
 * @return int The id of the newly inserted record.
 * @throws dml_exception
 * @throws moodle_exception
 */
function nextblocks_add_instance(object $moduleinstance, ?mod_nextblocks_mod_form $mform = null): int {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = (int)$DB->insert_record('nextblocks', $moduleinstance);

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        // If there is a cancel element on the form, and it was pressed,
        // then the `is_cancelled()` function will return true.
        // You can handle the cancel operation here.

        // Redirect to course page.
        redirect(new moodle_url('/course/view.php', ['id' => $moduleinstance->course]), 'Cancelled');

    } else if ($fromform = $mform->get_data()) {
        // When the form is submitted, and the data is successfully validated,
        // the `get_data()` function will return the data posted in the form.

        // Save custom blocks.
        nextblocks_save_custom_blocks($fromform, $id);

        if (nextblocks_has_tests_file($fromform)) {
            // Save the tests file in File API.
            nextblocks_save_tests_file($fromform, $id);

            // Save hash of the file in the database for later file retrieval.
            nextblocks_save_tests_file_hash($id);
        }

        $record = $DB->get_record('nextblocks', ['id' => $id]);
        nextblocks_grade_item_update($record);
    }


    // ...-----------------------Save block limits------------------------------

    foreach ($moduleinstance as $fieldname => $value) {
        if (strpos($fieldname, 'limit_') === 0) {
            $blocktype = substr($fieldname, 6);
            $infinite_field = 'infinite_' . $blocktype;

            if (!empty($moduleinstance->$infinite_field)) {
                continue;
            }

            if ($value !== '' && $value !== '0') {
                $limit = (int)$value;
                $record = (object)[
                    'nextblocksid' => $id,
                    'blocktype'    => $blocktype,
                    'blocklimit'   => $limit,
                ];
                $DB->insert_record('nextblocks_blocklimit', $record);
            }
        }
    }

    return $id;
}

/**
 * Handles grade updates
 *
 * @param $nextblocks object plugin instance
 * @param $userid int
 * @param $nullifnone bool
 * @return void
 */
function nextblocks_update_grades($nextblocks, $userid=0, $nullifnone=true) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    nextblocks_grade_item_update($nextblocks);
}

/**
 * Handles the grade update from a single submission
 *
 * @param $nextblocks object plugin instance
 * @param $grades array list of grades
 * @return int that submission's grade
 */
function nextblocks_grade_item_update($nextblocks, $grades=null): int {
    global $CFG;
    if (!function_exists('grade_update')) {
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (property_exists($nextblocks, 'cm_id')) {
        $params = ['itemname' => $nextblocks->name, 'idnumber' => $nextblocks->cm_id];
    } else {
        $params = ['itemname' => $nextblocks->name];
    }

    if ($nextblocks->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $nextblocks->grade;
        $params['grademin']  = 0;
    } else if ($nextblocks->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$nextblocks->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    } else if (!empty($grades)) {
        if (is_object($grades)) {
            $grades = [$grades->userid => $grades];
        } else if (array_key_exists('userid', $grades)) {
            $grades = [$grades['userid'] => $grades];
        }
        foreach ($grades as $key => $grade) {
            if (!is_array($grade)) {
                $grades[$key] = (array) $grade;
            }
        }
    }

    return grade_update('mod/nextblocks', $nextblocks->course, 'mod', 'nextblocks', $nextblocks->id, 0, $grades, $params);
}

/**
 * Saves custom blocks in the database
 *
 * @param object $fromform general data related to the blocks
 * @param int $id block id
 */
function nextblocks_save_custom_blocks(object $fromform, int $id) {
    global $DB;

    $blockdefinitions = $fromform->definition;
    $blockgenerators = $fromform->generator;
    $blockpythongenerators = $fromform->pythongenerator;

    if((count($blockdefinitions) == 1 && $blockdefinitions[0] == '') ||
        (count($blockgenerators) == 1 && $blockgenerators[0] == '')) {
        return;
    }

    if(count($blockdefinitions) !== count($blockgenerators)) {
        throw new dml_exception('Block definitions and generators do not match');
    }

    // Save each block definition and generator in the mdl_nextblocks_customblocks table.
    foreach ($blockdefinitions as $key => $blockdefinition) {
        $blockgenerator = $blockgenerators[$key];
        $blockpythongenerator = $blockpythongenerators[$key];
        $DB->insert_record('nextblocks_customblocks', ['blockdefinition' => $blockdefinition, 'blockgenerator' => $blockgenerator,
            'blockpythongenerator' => $blockpythongenerator, 'nextblocksid' => $id]);
    }
}

/**
 * Checks whether a test file is present
 *
 * @param object $fromform General data related to test file
 * @return bool true if test file is present, false otherwise
 */
function nextblocks_has_tests_file(object $fromform): bool {
    $files = file_get_all_files_in_draftarea($fromform->attachments);
    return count($files) > 0;
}

/**
 * Checks whether the file structure is valid
 *
 * @param string $file_string text inside file
 * @return bool true if structure is valid, false otherwise
 */
function nextblocks_file_structure_is_valid(string $filestring): bool {
    // Validate file structure with regular expression.
    $exp = "/(\|\s+(\w+\s+)*-\s+(\w+\s+)+)+/";
    return preg_match_all($exp, $filestring) !== 1;
}

/**
 * Converts test file to json
 *
 * @param int $id file id
 */
function nextblocks_convert_tests_file_to_json(int $id) {
    global $PAGE, $DB;
    $fileinfo = [
        'contextid' => $PAGE->context->id,
        'component' => 'mod_nextblocks',
        'filearea' => 'attachment',
        'itemid' => $id,
        'filepath' => '/',
        'filename' => 'nextblockstests'.$id.'.json',
    ];

    $fs = get_file_storage();
    $records = $DB->get_records_sql("
    SELECT *
    FROM {files}
    WHERE component = :component
      AND filearea = :filearea
      AND itemid = :itemid
      AND filename = :filename",
        [
            'component' => 'mod_nextblocks',
            'filearea' => 'attachment_txt',
            'itemid' => $id,
            'filename' => 'nextblockstests'.$id.'.txt',
        ]
    );

    if(empty($records)){
        return;
    }

    $rec = reset($records);

    $file = $fs->get_file(
        $rec->contextid,
        'mod_nextblocks',
        'attachment_txt',
        $id,
        $rec->filepath,
        $rec->filename
    );
    //Ignore the first line since its always the old context id.
    $filestring = preg_replace('/^.*?\R/', '', $file->get_content(), 1);

    // If it only had the contextid then there are no tests, no need to create json.
    if(empty($filestring)){
        return;
    }

    // Create json file.
    $json = nextblocks_parse_tests_file($filestring);
    $fs->create_file_from_string($fileinfo, json_encode($json));
}

/**
 * Saves the hash of the test file in the database
 * 
 * @param int $id id of the test file
 */
function nextblocks_save_tests_file_hash(int $id) {
    global $DB, $PAGE;
    $fs = get_file_storage();

    $files = $fs->get_area_files(
        $PAGE->context->id,
        'mod_nextblocks',
        'attachment',
        $id,
        'id',
        false
    );

    if (empty($files)) {
        return;
    }

    $file = reset($files);
    $pathnamehash = $file->get_pathnamehash();

    $DB->set_field('nextblocks', 'testsfilehash', $pathnamehash, ['id' => $id]);

}

/**
 * Gets the hash of the file name from a file id
 * 
 * @param int $id The id of the instance.
 * @return false|mixed The pathnamehash of the file or false if it does not exist.
 * @throws dml_exception
 */
function nextblocks_get_filenamehash(int $id) {
    global $PAGE;
    $fs = get_file_storage();
    $files = $fs->get_area_files(
        $PAGE->context->id,
        'mod_nextblocks',
        'attachment',
        $id,
        'id',
        false
    );
    if (empty($files)) {
        return false;
    }
    $file = reset($files);
    return $file->get_pathnamehash();
}

/**
 * Saves the tests file in the database
 * 
 * @param object $fromform file information
 * @param int $id file id
 */
function nextblocks_save_tests_file(object $fromform, int $id) {
    global $DB, $PAGE, $USER;

    $context = $PAGE->context;
    $usercontext = context_user::instance($USER->id);

    $fs = get_file_storage();

    $records = $DB->get_records_sql("
            SELECT *
            FROM {files}
            WHERE component = :component
              AND filearea = :filearea
              AND itemid = :itemid
              AND filename = :filename",
        [
            'component' => 'mod_nextblocks',
            'filearea' => 'attachment_txt',
            'itemid' => $id,
            'filename' => 'nextblockstests'.$id.'.txt',
        ]
    );

    if(!empty($records)){
        // Since there were previous files it means we are editing the exercise.
        // Delete the previous files to update them.
        $rec = reset($records);

        $file = $fs->get_file(
            $rec->contextid,
            'mod_nextblocks',
            'attachment_txt',
            $id,
            $rec->filepath,
            $rec->filename
        );

        $oldcontextid = (int)strtok($file->get_content(), "\n");

        $fs->delete_area_files($oldcontextid, 'mod_nextblocks', 'testfiles', $id);
        $fs->delete_area_files($context->id, 'mod_nextblocks', 'attachment', $id);
        $fs->delete_area_files($oldcontextid, 'mod_nextblocks', 'attachment_txt', $id);
    }
    else{
        // If there isnt a previous iteration then both contextids can be the same
        $oldcontextid = $context->id;
    }

    $draftfiles = $fs->get_area_files(
        $usercontext->id,
        'user',
        'draft',
        $fromform->attachments,
        'id',
        false
    );

    $testMap = [];

    foreach ($draftfiles as $file) {
        $filename = $file->get_filename();

        // Skip non-text files
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'txt') {
            continue;
        }

        $fs->create_file_from_storedfile([
            'contextid' => $oldcontextid,
            'component' => 'mod_nextblocks',
            'filearea'  => 'testfiles',
            'itemid'    => $id,
            'filepath'  => '/'
        ], $file);

        if (preg_match('/^input(\d+)\.txt$/i', $filename, $matches)) {
            $num = (int)$matches[1];
            $testMap[$num]['input'] = $filename;
        }
        elseif (preg_match('/^output(\d+)\.txt$/i', $filename, $matches)) {
            $num = (int)$matches[1];
            $testMap[$num]['output'] = $filename;
        }
    }

    ksort($testMap, SORT_NUMERIC);

    // Add the contextid as the header of the file, as it might be needed later to find the original test files
    $combinedContent = "{$oldcontextid}\n";

    foreach ($testMap as $num => $filenames) {
        if (empty($filenames['input']) || empty($filenames['output'])) {
            continue;
        }

        $inputFile = $fs->get_file(
            $oldcontextid,
            'mod_nextblocks',
            'testfiles',
            $id,
            '/',
            $filenames['input']
        );

        $outputFile = $fs->get_file(
            $oldcontextid,
            'mod_nextblocks',
            'testfiles',
            $id,
            '/',
            $filenames['output']
        );

        if (!$inputFile || !$outputFile) {
            continue;
        }

        $inputContent = $inputFile->get_content();
        $outputContent = $outputFile->get_content();

        $combinedContent .= "|\n" .
            rtrim($inputContent) . "\n-\n" .
            rtrim($outputContent) . "\n";
    }

    if (!empty($combinedContent)) {
        $fs->create_file_from_string([
            'contextid' => $oldcontextid,
            'component' => 'mod_nextblocks',
            'filearea'  => 'attachment_txt',
            'itemid'    => $id,
            'filepath'  => '/',
            'filename'  => "nextblockstests{$id}.txt"
        ], $combinedContent);
    }

    $fs->delete_area_files($usercontext->id, 'user', 'draft', $fromform->attachments);
}

/**
 * Parses the tests file into json
 * 
 * @param String $filestring The contents of the tests file
 * @return array [{}] An array of test cases, each test case containing a list of inputs and an output, in JSON format
 * @throws Exception If the file is not in the correct format
 */
function nextblocks_parse_tests_file(String $filestring): array {
    try {
        // The returned object has a list of test cases.
        $jsonreturn = [];

        // Different test cases are separated by |.
        $testcases = explode("|", $filestring);

        // File starts with a |, so the first element of the array is empty.
        array_shift($testcases);

        foreach ($testcases as $testcase) {
            // Each test case contains a list of inputs (and an output).
            $thistestcasejson = [];
            $thistestcasejson['inputs'] = [];

            // The input and output of the test are separated by -.
            $inputoutput = explode("-", $testcase);
            $inputs = $inputoutput[0];
            $thistestcasejson['output'] = trim($inputoutput[1]); // Remove newlines and add output of test to JSON.

            $inputlines = explode("_", $inputs);

            foreach ($inputlines as $input) {
                if (strlen($input) < 3) { // Skip junk elements.
                    continue;
                }
                // Each input has multiple lines. The first line is the input name and type, and the rest are
                // the input values for that input.
                $inputlines = array_map('trim', explode("\n", $input)); // Remove junk line breaks from every line.
                array_shift($inputlines); // Remove the first line (junk).
                array_pop($inputlines); // Remove the last line (junk).

                $inputName = explode(":", $inputlines[0])[0]; // Get the name of the input.

                $parts = explode(':', $inputlines[0], 2);
                $inputtype = trim($parts[1] ?? '');

                $inputvalue = [];
                $inputvalue[$inputtype] = array_slice($inputlines, 1); // Get the input values, skipping the first line.

                // Contains the input prompt and a list of input values.
                $thisinputjson = [$inputName => $inputvalue];
                $thistestcasejson['inputs'][] = $thisinputjson; // Add this input to the list of inputs of this test case.
            }
            $jsonreturn[] = $thistestcasejson; // Add this test case to the list of test cases.
        }
        return $jsonreturn;
    } catch (Exception $e) {
        throw new Exception("Error parsing tests file: " . $e->getMessage());
    }
}

/**
 * Updates an instance of the mod_nextblocks in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object                       $moduleinstance An object from the form in mod_form.php.
 * @param mod_nextblocks_mod_form|null $mform          The form.
 *
 * @return bool True if successful, false otherwise.
 * @throws dml_exception
 */
function nextblocks_update_instance(object $moduleinstance, ?mod_nextblocks_mod_form $mform = null): bool {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // ...-------------------Update block limits----------------------------

    $DB->delete_records('nextblocks_blocklimit', ['nextblocksid' => $moduleinstance->id]);

    foreach ($moduleinstance as $fieldname => $value) {
        if (strpos($fieldname, 'limit_') === 0) {
            $blocktype = substr($fieldname, 6);
            $infinite_field = 'infinite_' . $blocktype;

            // Check if this block is set to infinite
            if (!empty($moduleinstance->$infinite_field)) {
                // For infinite blocks, skip creating a limit record
                continue;
            }

            if ($value !== '' && $value !== '0') {
                $limit = (int)$value;
                $record = (object)[
                    'nextblocksid' => $moduleinstance->id,
                    'blocktype'    => $blocktype,
                    'blocklimit'   => $limit,
                ];
                $DB->insert_record('nextblocks_blocklimit', $record);
            }
        }
    }

    // ...--------------------------------Update tests file--------------------------.

    nextblocks_save_tests_file($moduleinstance, $moduleinstance->instance);

    // ...--------------------------------Update custom blocks-----------------------.

    $fromform = $mform->get_data();

    nextblocks_save_custom_blocks($fromform, $moduleinstance->id);

    return $DB->update_record('nextblocks', $moduleinstance);
}

/**
 * Removes an instance of the mod_nextblocks from the database.
 *
 * @param int $id Id of the module instance.
 *
 * @return bool True if successful, false on failure.
 * @throws dml_exception
 */
function nextblocks_delete_instance(int $id): bool {
    global $DB;

    $exists = $DB->get_record('nextblocks', ['id' => $id]);
    if (!$exists) {
        return false;
    }

    $DB->delete_records('nextblocks', ['id' => $id]);

    return true;
}

/**
 * Browser Console logging debug tool
 *
 * @param $output string output to debug
 * @param $withscripttags boolean
 */
function nextblocks_console_log($output, $withscripttags = true) {
    $jscode = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
        ');';
    if ($withscripttags) {
        $jscode = '<script>' . $jscode . '</script>';
    }
    echo $jscode;
}
