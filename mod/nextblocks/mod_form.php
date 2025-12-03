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
 * The main mod_nextblocks configuration form.
 * @package     mod_nextblocks
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package     mod_nextblocks
 * @copyright   2023 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_nextblocks_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     *
     * @throws coding_exception
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('nextblocksname', 'mod_nextblocks'), ['size' => '64']);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'nextblocksname', 'mod_nextblocks');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();

        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of mod_nextblocks settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.

        // ...<<------------------------------------------ Tests tab ------------------------------------------>>//

        $mform->addElement('header', 'tests', get_string('nextblockscreatetests', 'mod_nextblocks'));

        $mform->addElement(
            'filemanager',
            'attachments',
            get_string('testsfilesubmit', 'mod_nextblocks'),
            null,
            [
                'subdirs' => 0,
                'areamaxbytes' => 10485760,
                'maxfiles' => -1,
                'accepted_types' => ['.txt'],
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );
        $mform->addHelpButton('attachments', 'testsfile', 'mod_nextblocks');
        $mform->setType('attachments', PARAM_INT);

        // ...<<------------------------------------------ Custom Blocks tab ------------------------------------------>>//

        /**
         * Renders the custom blocks
         * 
         * @param $mform object
         * @return void
         */
        function addcustomblockinputs($mform) {
            $mform->addElement('textarea', 'blockdefinition', get_string("blockdefinition", "mod_nextblocks"),
                'wrap="virtual" rows="8" cols="80"');
            $mform->addHelpButton('blockdefinition', 'blockdefinition', 'mod_nextblocks');
            $mform->setType('blockdefinition', PARAM_TEXT);
            $mform->addElement('textarea', 'blockgenerator', get_string("blockgenerator", "mod_nextblocks"),
                'wrap="virtual" rows="8" cols="80"');
            $mform->addHelpButton('blockgenerator', 'blockgenerator', 'mod_nextblocks');
            $mform->setType('blockgenerator', PARAM_TEXT);
            $mform->addElement('textarea', 'blockpythongenerator', get_string("blockpythongenerator", "mod_nextblocks"),
                'wrap="virtual" rows="8" cols="80"');
            $mform->addHelpButton('blockpythongenerator', 'blockpythongenerator', 'mod_nextblocks');
            $mform->setType('blockpythongenerator', PARAM_TEXT);
        }

        $mform->addElement('header', 'customblocks', get_string('nextblockscreatecustomblocks', 'mod_nextblocks'));
        $mform->addElement('html', get_string('customblockstext', 'mod_nextblocks'));

        $repeatarray = [
            $mform->createElement('textarea', 'definition', get_string('blockdefinition', 'mod_nextblocks'),
                'wrap="virtual" rows="8" cols="80"'),
            $mform->createElement('textarea', 'generator', get_string('blockgenerator', 'mod_nextblocks'),
                'wrap="virtual" rows="8" cols="80"'),
            $mform->createElement('textarea', 'pythongenerator', get_string('blockpythongenerator', 'mod_nextblocks'),
                'wrap="virtual" rows="8" cols="80"'),
            $mform->createElement('hidden', 'optionid', 0),
            $mform->createElement('submit', 'delete', get_string('deletestr', 'mod_nextblocks'), [], false),
        ];

        $repeatoptions = [
            'definition' => [
                'type' => PARAM_TEXT,

            ],
            'generator' => [
                'type' => PARAM_TEXT,
            ],
            'optionid' => [
                'type' => PARAM_INT,
            ],
        ];

        $this->repeat_elements(
            $repeatarray,
            1,
            $repeatoptions,
            'option_repeats',
            'option_add_fields',
            1,
            null,
            true,
            'delete',
        );

        // ...<<------------------------------------------ Block limits tab ------------------------------------------>>//

        global $DB;

        $mform->addElement('header', 'hdr_blocklimits', 'Block Limits');

        $builtincategories = [
            // Logic
            get_string('category_logic', 'mod_nextblocks') => [
                'controls_if'       => get_string('controls_if',        'mod_nextblocks'),
                'logic_compare'     => get_string('logic_compare',      'mod_nextblocks'),
                'logic_negate'      => get_string('logic_negate',       'mod_nextblocks'),
                'logic_operation'   => get_string('logic_operation',    'mod_nextblocks'),
                'logic_boolean'     => get_string('logic_boolean',      'mod_nextblocks'),
                'logic_null'        => get_string('logic_null',         'mod_nextblocks'),
                'logic_ternary'     => get_string('logic_ternary',      'mod_nextblocks'),
            ],

            // Loops
            get_string('category_loops', 'mod_nextblocks') => [
                'controls_repeat_ext'      => get_string('controls_repeat_ext',     'mod_nextblocks'),
                'controls_whileUntil'      => get_string('controls_whileUntil',     'mod_nextblocks'),
                'controls_for'             => get_string('controls_for',              'mod_nextblocks'),
                'controls_forEach'         => get_string('controls_forEach',          'mod_nextblocks'),
                'controls_flow_statements' => get_string('controls_flow_statements','mod_nextblocks'),
            ],

            // Math
            get_string('category_math', 'mod_nextblocks') => [
                'math_number'         => get_string('math_number',        'mod_nextblocks'),
                'math_arithmetic'     => get_string('math_arithmetic',    'mod_nextblocks'),
                'math_single'         => get_string('math_single',        'mod_nextblocks'),
                'math_trig'           => get_string('math_trig',          'mod_nextblocks'),
                'math_constant'       => get_string('math_constant',      'mod_nextblocks'),
                'math_number_property'=> get_string('math_number_property','mod_nextblocks'),
                'math_round'          => get_string('math_round',         'mod_nextblocks'),
                'math_on_list'        => get_string('math_on_list',       'mod_nextblocks'),
                'math_modulo'         => get_string('math_modulo',        'mod_nextblocks'),
                'math_constrain'      => get_string('math_constrain',     'mod_nextblocks'),
                'math_random_int'     => get_string('math_random_int',    'mod_nextblocks'),
                'math_random_float'   => get_string('math_random_float',  'mod_nextblocks'),
                'math_atan2'          => get_string('math_atan2',         'mod_nextblocks'),
                'text_to_number'      => get_string('text_to_number',     'mod_nextblocks'),
            ],

            // Text
            get_string('category_text', 'mod_nextblocks') => [
                'text'              => get_string('text',               'mod_nextblocks'),
                'text_multiline'    => get_string('text_multiline',     'mod_nextblocks'),
                'text_join'         => get_string('text_join',          'mod_nextblocks'),
                'text_append'       => get_string('text_append',        'mod_nextblocks'),
                'text_length'       => get_string('text_length',        'mod_nextblocks'),
                'text_isEmpty'      => get_string('text_isEmpty',       'mod_nextblocks'),
                'text_indexOf'      => get_string('text_indexOf',       'mod_nextblocks'),
                'text_charAt'       => get_string('text_charAt',        'mod_nextblocks'),
                'text_getSubstring' => get_string('text_getSubstring',  'mod_nextblocks'),
                'text_changeCase'   => get_string('text_changeCase',    'mod_nextblocks'),
                'text_trim'         => get_string('text_trim',          'mod_nextblocks'),
                'text_count'        => get_string('text_count',         'mod_nextblocks'),
                'text_replace'      => get_string('text_replace',       'mod_nextblocks'),
                'text_reverse'      => get_string('text_reverse',       'mod_nextblocks'),
            ],

            // Lists
            get_string('category_lists', 'mod_nextblocks') => [
                'lists_create_with' => get_string('lists_create_with',  'mod_nextblocks'),
                'lists_repeat'      => get_string('lists_repeat',       'mod_nextblocks'),
                'lists_length'      => get_string('lists_length',       'mod_nextblocks'),
                'lists_isEmpty'     => get_string('lists_isEmpty',      'mod_nextblocks'),
                'lists_indexOf'     => get_string('lists_indexOf',      'mod_nextblocks'),
                'lists_getIndex'    => get_string('lists_getIndex',     'mod_nextblocks'),
                'lists_setIndex'    => get_string('lists_setIndex',     'mod_nextblocks'),
                'lists_getSublist'  => get_string('lists_getSublist',   'mod_nextblocks'),
                'lists_split'       => get_string('lists_split',        'mod_nextblocks'),
                'lists_sort'        => get_string('lists_sort',         'mod_nextblocks'),
                'lists_reverse'     => get_string('lists_reverse',      'mod_nextblocks'),
            ],

            // Input/Output
            get_string('category_io', 'mod_nextblocks') => [
                'text_print'        => get_string('text_print',         'mod_nextblocks'),
                'text_ask'          => get_string('text_ask',           'mod_nextblocks'),
            ],

            // Variables
            get_string('category_variables', 'mod_nextblocks') => [
                'variables_get' => get_string('variables_get', 'mod_nextblocks'),
                'variables_set' => get_string('variables_set', 'mod_nextblocks'),
            ],

            // Functions
            get_string('category_functions', 'mod_nextblocks') => [
                'procedures_defnoreturn'  => get_string('procedures_defnoreturn',  'mod_nextblocks'),
                'procedures_defreturn'    => get_string('procedures_defreturn',    'mod_nextblocks'),
                'procedures_callnoreturn' => get_string('procedures_callnoreturn','mod_nextblocks'),
                'procedures_callreturn'   => get_string('procedures_callreturn',   'mod_nextblocks'),
                'procedures_ifreturn'     => get_string('procedures_ifreturn',     'mod_nextblocks'),
            ],
        ];

        // Get previously created limits if the exercise was already created and is being edited.
        $existing_limits = [];
        $limits = $DB->get_records('nextblocks_blocklimit', ['nextblocksid' => $this->current->instance]);
        foreach ($limits as $limit) {
            $existing_limits[$limit->blocktype] = $limit->blocklimit;
        }

        foreach ($builtincategories as $catname => $blocks) {
            $mform->addElement('html',
                '<details style="margin-top:1em;"><summary><strong>'
                . $catname .
                '</strong></summary>'
            );

            foreach ($blocks as $type => $label) {
                $field = 'limit_' . $type;
                $infinite_field = 'infinite_' . $type;

                $group = [];
                $group[] = $mform->createElement('checkbox', $infinite_field, '', get_string('infinite', 'mod_nextblocks'));

                $text_element = $mform->createElement('text', $field, '',
                    ['size' => 4, 'class' => 'nextblocks-limit-input']
                );
                $group[] = $text_element;

                $mform->addGroup($group, $field.'_group', $label, ' ', false);
                $mform->setType($field, PARAM_INT);

                if (array_key_exists($type, $existing_limits)) {
                    $mform->setDefault($infinite_field, 0);
                    $mform->setDefault($field, $existing_limits[$type]);
                } else {
                    $mform->setDefault($infinite_field, 1); // Default: checked
                    $mform->setDefault($field, 0);
                }
            }
            $mform->addElement('html', '</details>');
        }

        if(!empty($this->current->instance)) {
            $customrecs = $DB->get_records('nextblocks_customblocks',
                ['nextblocksid' => $this->current->instance]
            );
            if ($customrecs) {
                $mform->addElement('html',
                    '<details style="margin-top:1em;"><summary><strong>'
                    . get_string('customblocks', 'mod_nextblocks') .
                    '</strong></summary>'
                );
                foreach ($customrecs as $rec) {
                    $def = json_decode($rec->blockdefinition, true);
                    if (!empty($def['type'])) {
                        $label = !empty($def['message0']) ? $def['message0'] : $def['type'];
                        $field = 'limit_' . $def['type'];
                        $infinite_field = 'infinite_' . $def['type'];

                        $group = [];
                        $group[] = $mform->createElement('checkbox', $infinite_field, '', get_string('infinite', 'mod_nextblocks'));

                        $text_element = $mform->createElement('text', $field, '',
                            ['size' => 4, 'class' => 'nextblocks-limit-input']
                        );
                        $group[] = $text_element;

                        $mform->addGroup($group, $field.'_group', $label, ' ', false);
                        $mform->setType($field, PARAM_INT);

                        if (array_key_exists($def['type'], $existing_limits)) {
                            $mform->setDefault($infinite_field, 0);
                            $mform->setDefault($field, $existing_limits[$def['type']]);
                        } else {
                            $mform->setDefault($infinite_field, 1); // Default: checked
                            $mform->setDefault($field, 0);
                        }
                    }
                }
                $mform->addElement('html', '</details>');
            }
        }

        $mform->addElement('html', '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateLimitVisibility() {
                document.querySelectorAll("input[type=\"checkbox\"][name^=\"infinite_\"]").forEach(checkbox => {
                    const fieldName = checkbox.name.replace("infinite_", "limit_");
                    const textInput = document.querySelector(`input[name="${fieldName}"]`);
                    
                    if (textInput) {
                        textInput.style.display = checkbox.checked ? "none" : "inline-block";
                    }
                });
            }
            
            updateLimitVisibility();
            
            document.querySelectorAll("input[type=\"checkbox\"][name^=\"infinite_\"]").forEach(checkbox => {
                checkbox.addEventListener("change", updateLimitVisibility);
            });
            
            document.querySelectorAll("details").forEach(details => {
                details.addEventListener("toggle", updateLimitVisibility);
            });
            
            const form = document.querySelector("form.mform");
            if (form) {
                form.addEventListener("submit", function() {
                    document.querySelectorAll("input[name^=\"limit_\"]").forEach(input => {
                        input.style.display = "inline-block";
                    });
                });
            }
        });
        </script>
        ');

        $mform->addElement('html', '<style>
        input[name^="limit_"] {
            display: inline-block; 
            width: 50px; 
        }
        </style>');

        // ...<<------------------------------------------ Submissions tab ------------------------------------------>>//

        $mform->addElement(
            'header', 'submissions', get_string('nextblockscreatesubmissions', 'mod_nextblocks')
        );

        $mform->addElement(
            'advcheckbox', 'multiplesubmissions', get_string('multiplesubmissions', 'mod_nextblocks'),
        );
        $mform->addElement('text', 'maxsubmissions', get_string('howmanysubmissions', 'mod_nextblocks'));
        $mform->setType('maxsubmissions', PARAM_INT);
        $mform->hideIf('maxsubmissions', 'multiplesubmissions', 'neq', 1);

        // ...<<------------------------------------------ Grading tab ------------------------------------------>>//

        $this->standard_grading_coursemodule_elements();

        $this->standard_coursemodule_elements();
        $this->apply_admin_defaults();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;

        if (isset($defaultvalues['maxsubmissions'])) {
            $defaultvalues['multiplesubmissions'] = ($defaultvalues['maxsubmissions'] != 1) ? 1 : 0;
        }

        $fs = get_file_storage();

        if ($this->current && $this->current->instance) {

            $id = $this->current->instance;
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

            $oldcontextid = (int)strtok($file->get_content(), "\n");

            $draftitemid = file_get_submitted_draft_itemid('attachments');

            file_prepare_draft_area(
                $draftitemid,
                $oldcontextid,
                'mod_nextblocks',
                'testfiles',  // Source area
                $id,
                ['subdirs' => 0, 'maxfiles' => -1]
            );

            $defaultvalues['attachments'] = $draftitemid;

        }
        return $defaultvalues;
    }

    /**
     * Checks whether file structure is correct
     *
     * @param $data
     * @param $files
     * @return array errors
     */
    public function validation($data, $files): array {
        global $USER;
        $errors = parent::validation($data, $files);
        $usercontext = context_user::instance($USER->id);
        $fs = get_file_storage();
        try {
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $data['attachments'], 'id', false);
        } catch (coding_exception $e) {
            // If no files just continue.
        }
        if (count($files) === 1) {
            $file = reset($files);
            $filestring = $file->get_content();
            if (nextblocks_file_structure_is_valid($filestring)) {
                $errors['attachments'] = get_string('invalidfilestructure', 'mod_nextblocks');
            }
        }

        return $errors;
    }

    /**
     * Overrides original function to make sure that if the "multiple submissions" checkbox is unchecked then maxsubmissions is 1
     * @return mixed
     */
    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            if (empty($data->multiplesubmissions)) {
                $data->maxsubmissions = 1;
            }
        }
        return $data;
    }
}
