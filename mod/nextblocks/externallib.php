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

global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once(__DIR__ . '/lib.php');

/**
 * Handles communications with external sources
 */
class mod_nextblocks_external extends external_api {

    /**
     * Saves the workspace of a user.
     *
     * @param int    $nextblocksid Id of the nextblocks activity
     * @param string $savedworkspace The workspace to be saved, in base64
     * @param int    $userid          The id of the user that is saving the workspace.
     *                                By default is not needed, and the current user is used.
     *                                Only used when teacher is adding comments to user's workspace.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function save_workspace($nextblocksid, $savedworkspace, $userid=null) {
        global $DB, $USER;
        if (!$userid) {
            $userid = $USER->id;
        }

        $cm = get_coursemodule_from_id('nextblocks', $nextblocksid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $params = self::validate_parameters(self::save_workspace_parameters(),
            ['nextblocksid' => $nextblocksid, 'saved_workspace' => $savedworkspace]);
        // Check if record exists.
        $record = $DB->get_record('nextblocks_userdata', ['userid' => $userid, 'nextblocksid' => $cm->instance]);
        // If record exists with same userid and nextblocksid, update it, else insert new record.
        if ($record) {
            $DB->update_record('nextblocks_userdata', ['id' => $record->id, 'userid' => $userid,
                'nextblocksid' => $cm->instance, 'saved_workspace' => $savedworkspace]);
        } else {
            $DB->insert_record('nextblocks_userdata', ['userid' => $userid,
                'nextblocksid' => $cm->instance, 'saved_workspace' => $savedworkspace]);
        }
    }

    /**
     * Saves needed workspace parameters
     * 
     * @return external_function_parameters
     */
    public static function save_workspace_parameters() {
        return new external_function_parameters(
            [
                'nextblocksid' => new external_value(PARAM_INT, 'module id', VALUE_REQUIRED),
                'saved_workspace' => new external_value(PARAM_RAW, 'workspace', VALUE_REQUIRED),
                'userid' => new external_value(PARAM_INT, 'user id', VALUE_OPTIONAL, 0),
            ]
        );
    }

    /**
     * Nothing needs to be done
     * 
     * @return null
     */
    public static function save_workspace_returns() {
        return null;
    }

    /**
     * Handles program submission when user clicks "Submit" button
     *
     * @param $nextblocksid int activity instance id
     * @param $submittedworkspace object program to submit
     * @param $codestring string javascript code generated from program
     */
    public static function submit_workspace($nextblocksid, $submittedworkspace, $codestring) {
        global $DB, $USER, $PAGE;

        $cm = get_coursemodule_from_id('nextblocks', $nextblocksid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $params = self::validate_parameters(self::submit_workspace_parameters(),
            ['nextblocksid' => $nextblocksid, 'submitted_workspace' => $submittedworkspace, 'codeString' => $codestring]);
        // Check if record exists.
        $record = $DB->get_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance]);
        // If record exists with same userid and nextblocksid, update it, else insert new record.
        if ($record) {
            $DB->update_record('nextblocks_userdata', ['id' => $record->id, 'userid' => $USER->id, 'nextblocksid' => $cm->instance,
                'saved_workspace' => $submittedworkspace, 'submitted_workspace' => $submittedworkspace, 
                'submissionnumber' => $record->submissionnumber + 1]);
        } else {
            $DB->insert_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance,
                'saved_workspace' => $submittedworkspace, 'submitted_workspace' => $submittedworkspace, 'submissionnumber' => 1]);
        }

        $nextblocks = $DB->get_record('nextblocks', ['id' => $cm->instance]);

        $PAGE->set_context($context);

        $fs = get_file_storage();
        $filenamehash = nextblocks_get_filenamehash($cm->instance);

        // If has point grade and tests, run auto grading.
        if ($nextblocks->grade > 0 && $filenamehash != false) {
            $testsfile = $fs->get_file_by_hash($filenamehash);
            self::auto_grade($cm, $codestring, $nextblocks, $testsfile);
        }
    }

    /**
     * Automatically grade students' submissions using test system
     *
     * @param $cm object context module
     * @param $codestring string javascript code generated from program
     * @param $nextblocks object plugin instance
     * @param $testsfile object tests file
     */
    public static function auto_grade($cm, $codestring, $nextblocks, $testsfile) {
        global $USER, $DB;

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $testsfilecontents = $testsfile->get_content();
        $tests = json_decode($testsfilecontents, true);
        $testscount = count($tests);
        $testscorrectcount = self::run_tests_judge0($cm, $tests, $codestring);
        $newgrade = $testscorrectcount / $testscount * $nextblocks->grade; // ...$nextblocks->grade is the max grade.

        $grades = new stdClass();
        $grades->userid = $USER->id;
        $grades->rawgrade = $newgrade;

        nextblocks_grade_item_update($nextblocks, $grades);

        // Update userdata with new grade.
        $userdata = $DB->get_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance]);
        $DB->update_record('nextblocks_userdata', ['id' => $userdata->id, 'userid' => $USER->id,
            'nextblocksid' => $cm->instance, 'grade' => $newgrade]);
    }

    /**
     * Run all tests via Judge0.
     *
     * @param object $cm context module
     * @param array  $tests      Array of tests (with 'inputs' and 'output').
     * @param string $codestring The raw code.
     * @return int               Number of tests passed.
     */
    public static function run_tests_judge0($cm, $tests, $codestring) {

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $submissions = [];
        $languageid = "102"; // JavaScript (Node.js 22.08.0)
        foreach ($tests as $test) {
            $inputs = $test['inputs'];
            foreach ($inputs as $input) {
                $prompt = array_keys($input)[0];
                $values = array_values($input[$prompt])[0];
                $combination = array_merge([$prompt], $values);
            }
            $inputstring = "";
            foreach($combination as $c){
                $inputstring .= $c;
                $inputstring .= "\n";
            }

            if(strlen($inputstring) != 0){
                $inputstring = substr($inputstring, 0, strlen($inputstring)-1);
            }
            $output = $test['output'];
            
            $submissions[] = [
                'expected_output' => $output,
                'language_id'     => $languageid,
                'source_code'     => $codestring,
                'stdin'           => $inputstring
            ];
        }

        $curl = new \curl();
        $headers = [
            "Accept: application/json",
            "Authorization: Bearer sk_live_spZDwoQjgwyN4GTGuNoSrdy8qCdU3vUD",
            "Content-Type: application/json"
        ];

        $response = $curl->post("https://judge0-ce.p.sulu.sh/submissions/batch",
            json_encode(['submissions' => $submissions]),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        sleep(5); // Give API time to execute the tests.

        $passed = 0;
        $tokens = array_column(json_decode($response, true), 'token');
        $tokenlist = implode(',', $tokens);
        $geturl = "https://judge0-ce.p.sulu.sh/submissions/batch?tokens={$tokenlist}";
        $resp2 = $curl->get($geturl, ['CURLOPT_HTTPHEADER' => $headers]);
        $results = json_decode($resp2, true);
        if (empty($results['submissions']) || !is_array($results['submissions'])) {
            throw new \moodle_exception('judge0timeout', 'mod_nextblocks');
        }

        foreach ($results['submissions'] as $res) {
            if (isset($res['status']['id']) && (int)$res['status']['id'] === 3) {
                $passed++;
            }
        }

        return $passed;
    }

    /**
     * @return external_function_parameters
     */
    public static function submit_workspace_parameters() {
        return new external_function_parameters(
            [
                'nextblocksid' => new external_value(PARAM_INT, 'module id'),
                'submitted_workspace' => new external_value(PARAM_RAW, 'workspace'),
                'codeString' => new external_value(PARAM_RAW, 'codeString'),
            ]
        );
    }

    /**
     * @return null
     */
    public static function submit_workspace_returns() {
        return null;
    }

    /**
     * Submits a reaction to an exercise made by a user
     * 
     * @param $nextblocksid int the id of the activity
     * @param $reaction string the reaction done by the user (easy, medium, hard)
     * @return array reaction counter
     */
    public static function submit_reaction($nextblocksid, $reaction) {
        global $DB, $USER;

        $cm = get_coursemodule_from_id('nextblocks', $nextblocksid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $params = self::validate_parameters(self::submit_reaction_parameters(),
            ['nextblocksid' => $nextblocksid, 'reaction' => $reaction]);

        $nextblocks = $DB->get_record('nextblocks', ['id' => $cm->instance]);
        $userdata = $DB->get_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance]);
        // If userdata does not exist, insert new record.
        if (!$userdata) {
            $newid = $DB->insert_record('nextblocks_userdata', ['userid' => $USER->id, 'nextblocksid' => $cm->instance]);
            $userdata = $DB->get_record('nextblocks_userdata', ['id' => $newid]);
        }

        // Get reaction database column name.
        $newreactioncolumnname = "reactions".$reaction;
        // Reaction to number, for database (easy-1, medium-2, hard-3).
        $newreactionnumber = array_search($reaction, ['easy', 'medium', 'hard']) + 1;

        $newreactions = [
            'reactionseasy' => $nextblocks->reactionseasy,
            'reactionsmedium' => $nextblocks->reactionsmedium,
            'reactionshard' => $nextblocks->reactionshard,
        ];

        // If new reaction is same as previous reaction, decrement reaction.
        if ($userdata->reacted == $newreactionnumber) {
            $DB->update_record('nextblocks', ['id' => $nextblocks->id, 
                $newreactioncolumnname => $nextblocks->$newreactioncolumnname - 1]);
            // User unreacted, update userdata.
            $DB->update_record('nextblocks_userdata', ['id' => $userdata->id, 'userid' => $USER->id, 
                'nextblocksid' => $cm->instance, 'reacted' => 0]);
            $newreactions[$newreactioncolumnname] = $nextblocks->$newreactioncolumnname - 1;
        } else { // Else, decrement previous reaction (if it exists) and increment new reaction.
            if ($userdata->reacted == 0) {
                $DB->update_record('nextblocks', ['id' => $nextblocks->id, 
                    $newreactioncolumnname => $nextblocks->$newreactioncolumnname + 1]);
                $newreactions[$newreactioncolumnname] = $nextblocks->$newreactioncolumnname + 1;
            } else {
                $oldreactioncolumnname = "reactions" . ['easy', 'medium', 'hard'][$userdata->reacted - 1];

                $DB->update_record(
                    'nextblocks', [
                        'id' => $nextblocks->id,
                        $newreactioncolumnname => $nextblocks->$newreactioncolumnname + 1,
                        $oldreactioncolumnname => $nextblocks->$oldreactioncolumnname - 1,
                    ]
                );
                $newreactions[$newreactioncolumnname] = $nextblocks->$newreactioncolumnname + 1;
                $newreactions[$oldreactioncolumnname] = $nextblocks->$oldreactioncolumnname - 1;
            }
            // Update userdata with new reaction.
            $DB->update_record('nextblocks_userdata', ['id' => $userdata->id, 'userid' => $USER->id, 
                'nextblocksid' => $cm->instance, 'reacted' => $newreactionnumber]);
        }

        return $newreactions;
    }

    /**
     * @return external_function_parameters
     */
    public static function submit_reaction_parameters() {
        return new external_function_parameters(
            [
                'nextblocksid' => new external_value(PARAM_INT, 'module id'),
                'reaction' => new external_value(PARAM_ALPHA, 'workspace'),
            ]
        );
    }

    /**
     * @return external_single_structure
     */
    public static function submit_reaction_returns() {
        return new external_single_structure(
            [
                'reactionseasy' => new external_value(PARAM_INT, 'number of easy reactions'),
                'reactionsmedium' => new external_value(PARAM_INT, 'number of medium reactions'),
                'reactionshard' => new external_value(PARAM_INT, 'number of hard reactions'),
            ]
        );
    }

    /**
     * Saves a message sent by a user in the database
     * 
     * @param $message string 
     * @param $username string
     * @param $nextblocksid int
     * @param $cmid int
     * @param $timestamp int
     */
    public static function save_message($message, $username, $nextblocksid, $cmid, $timestamp) {
        global $DB;

        $cm = get_coursemodule_from_id('nextblocks', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $params = self::validate_parameters(self::save_message_parameters(),
            ['message' => $message, 'username' => $username, 'nextblocksid' => $nextblocksid, 'cmid' => $cmid, 'timestamp' => $timestamp]);
        $DB->insert_record('nextblocks_messages', ['message' => $message, 'username' => $username, 
            'nextblocksid' => $nextblocksid, 'timestamp' => $timestamp]);
    }

    /**
     * @return external_function_parameters
     */
    public static function save_message_parameters() {
        return new external_function_parameters(
            [
                'message' => new external_value(PARAM_TEXT, 'message sent'),
                'username' => new external_value(PARAM_TEXT, 'name of the user who sent the message'),
                'nextblocksid' => new external_value(PARAM_INT, 'id of the activity where the message was sent'),
                'cmid' => new external_value(PARAM_INT, 'context module id'),
                'timestamp' => new external_value(PARAM_INT, 'when the message was sent (UNIX time)'),
            ]
        );
    }

    /**
     * @return null
     */
    public static function save_message_returns() {
        return null;
    }

    /**
     * Gets a specific number of messages in an activity through the database
     * 
     * @param $messagecount int number of messages to get
     * @param $nextblocksid int activity id
     * @param $cmid int context module id
     * @return array list of messages
     */
    public static function get_messages($messagecount, $nextblocksid, $cmid) {
        global $DB;

        $cm = get_coursemodule_from_id('nextblocks', $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $params = self::validate_parameters(self::get_messages_parameters(),
            ['messagecount' => $messagecount, 'nextblocksid' => $nextblocksid, 'cmid' => $cmid]);
        $messages = $DB->get_records('nextblocks_messages', ['nextblocksid' => $nextblocksid],
            'timestamp ASC', '*', 0, $messagecount);
        $messagesarray = [];
        foreach ($messages as $message) {
            $messagearray = [
                'message' => $message->message,
                'username' => $message->username,
                'timestamp' => $message->timestamp,
            ];
            $messagesarray[] = $messagearray;
        }
        return $messagesarray;
    }

    /**
     * @return external_function_parameters
     */
    public static function get_messages_parameters() {
        return new external_function_parameters(
            [
                'messagecount' => new external_value(PARAM_INT, 'number of messages to get'),
                'nextblocksid' => new external_value(PARAM_INT, 'id of the activity where the messages were sent'),
                'cmid' => new external_value(PARAM_INT, 'context module id'),
            ]
        );
    }

    /**
     * @return external_multiple_structure
     */
    public static function get_messages_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'message' => new external_value(PARAM_TEXT, 'message sent'),
                    'username' => new external_value(PARAM_TEXT, 'name of the user who sent the message'),
                    'timestamp' => new external_value(PARAM_INT, 'when the message was sent (UNIX time)'),
                ]
            )
        );
    }

    public static function get_comments_parameters() {
        return new external_function_parameters([
            'blockid' => new external_value(PARAM_RAW, get_string('blockid')),
            'cmid' => new external_value(PARAM_INT, get_string('cmid')),
        ]);
    }

    public static function get_comments($blockid, $cmid) {
        global $DB;

        $params = self::validate_parameters(self::get_comments_parameters(), [
            'blockid' => $blockid,
            'cmid' => $cmid
        ]);
        $blockid = $params['blockid'];

        $context = context_module::instance($params['cmid']);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        if (empty($blockid) || strlen($blockid) > 255) {
            throw new invalid_parameter_exception('Invalid block ID');
        }

        $comments = $DB->get_records_sql("
            SELECT c.*, u.firstname, u.lastname 
            FROM {blockly_comments} c
            JOIN {user} u ON u.id = c.userid
            WHERE blockid = :blockid
            AND contextid = :contextid
            ORDER BY timecreated DESC",
            ['blockid' => $blockid, 'contextid' => $context->id]
        );

        $result = [];
        foreach ($comments as $comment) {
            $result[] = [
                'id' => $comment->id,
                'blockid' => $comment->blockid,
                'userid' => $comment->userid,
                'content' => $comment->content,
                'timecreated' => $comment->timecreated,
                'firstname' => $comment->firstname,
                'lastname' => $comment->lastname,
            ];
        }

        return $result;
    }

    public static function get_comments_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, get_string('commentid')),
                'blockid' => new external_value(PARAM_RAW, get_string('blockid')),
                'userid' => new external_value(PARAM_INT, get_string('userid')),
                'content' => new external_value(PARAM_TEXT, get_string('commentcontent')),
                'timecreated' => new external_value(PARAM_INT, get_string('timestamp')),
                'firstname' => new external_value(PARAM_TEXT, get_string('firstname')),
                'lastname' => new external_value(PARAM_TEXT, get_string('lastname')),
            ])
        );
    }

    public static function save_comment_parameters() {
        return new external_function_parameters([
            'blockid' => new external_value(PARAM_RAW, get_string('blockid')),
            'content' => new external_value(PARAM_TEXT, get_string('commentcontent')),
            'cmid' => new external_value(PARAM_INT, get_string('cmid')),
        ]);
    }

    public static function save_comment($blockid, $content, $cmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::save_comment_parameters(), [
            'blockid' => $blockid,
            'content' => $content,
            'cmid' => $cmid
        ]);

        $context = context_module::instance($params['cmid']);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        $record = new stdClass();
        $record->blockid = $params['blockid'];
        $record->userid = $USER->id;
        $record->content = $params['content'];
        $record->timecreated = time();
        $record->contextid = $context->id;

        $DB->insert_record('blockly_comments', $record);

        return true;
    }

    public static function save_comment_returns() {
        return new external_value(PARAM_BOOL, get_string('successstatus'));
    }

    public static function get_all_block_comments_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, get_string('cmid'))
        ]);
    }

    public static function get_all_block_comments($cmid) {
        global $DB;
        $context = context_module::instance($cmid);
        self::validate_context($context);
        require_capability('mod/nextblocks:view', $context);

        return $DB->get_fieldset_sql("
        SELECT DISTINCT blockid
        FROM {blockly_comments}
        WHERE contextid = :contextid
    ", ['contextid' => $context->id]);
    }

    public static function get_all_block_comments_returns() {
        return new external_multiple_structure(
            new external_value(PARAM_RAW, get_string('blockid'))
        );
    }

}
