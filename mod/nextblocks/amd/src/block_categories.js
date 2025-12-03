/**
 *
 * @module      mod_nextblocks/block_categories
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str'], function(str) {
    const stringIds = [
        // Categories
        {key: 'category_logic', component: 'mod_nextblocks'},
        {key: 'category_loops', component: 'mod_nextblocks'},
        {key: 'category_math', component: 'mod_nextblocks'},
        {key: 'category_text', component: 'mod_nextblocks'},
        {key: 'category_lists', component: 'mod_nextblocks'},
        {key: 'category_io', component: 'mod_nextblocks'},
        {key: 'category_variables', component: 'mod_nextblocks'},
        {key: 'category_functions', component: 'mod_nextblocks'},

        // Logic Blocks
        {key: 'controls_if', component: 'mod_nextblocks'},
        {key: 'logic_compare', component: 'mod_nextblocks'},
        {key: 'logic_negate', component: 'mod_nextblocks'},
        {key: 'logic_operation', component: 'mod_nextblocks'},
        {key: 'logic_boolean', component: 'mod_nextblocks'},
        {key: 'logic_null', component: 'mod_nextblocks'},
        {key: 'logic_ternary', component: 'mod_nextblocks'},

        // Loops Blocks
        {key: 'controls_repeat_ext', component: 'mod_nextblocks'},
        {key: 'controls_whileUntil', component: 'mod_nextblocks'},
        {key: 'controls_for', component: 'mod_nextblocks'},
        {key: 'controls_forEach', component: 'mod_nextblocks'},
        {key: 'controls_flow_statements', component: 'mod_nextblocks'},

        // Math Blocks
        {key: 'math_number', component: 'mod_nextblocks'},
        {key: 'math_arithmetic', component: 'mod_nextblocks'},
        {key: 'math_single', component: 'mod_nextblocks'},
        {key: 'math_trig', component: 'mod_nextblocks'},
        {key: 'math_constant', component: 'mod_nextblocks'},
        {key: 'math_number_property', component: 'mod_nextblocks'},
        {key: 'math_round', component: 'mod_nextblocks'},
        {key: 'math_on_list', component: 'mod_nextblocks'},
        {key: 'math_modulo', component: 'mod_nextblocks'},
        {key: 'math_constrain', component: 'mod_nextblocks'},
        {key: 'math_random_int', component: 'mod_nextblocks'},
        {key: 'math_random_float', component: 'mod_nextblocks'},
        {key: 'math_atan2', component: 'mod_nextblocks'},
        {key: 'text_to_number', component: 'mod_nextblocks'},

        // Text Blocks
        {key: 'text', component: 'mod_nextblocks'},
        {key: 'text_multiline', component: 'mod_nextblocks'},
        {key: 'text_join', component: 'mod_nextblocks'},
        {key: 'text_append', component: 'mod_nextblocks'},
        {key: 'text_length', component: 'mod_nextblocks'},
        {key: 'text_isEmpty', component: 'mod_nextblocks'},
        {key: 'text_indexOf', component: 'mod_nextblocks'},
        {key: 'text_charAt', component: 'mod_nextblocks'},
        {key: 'text_getSubstring', component: 'mod_nextblocks'},
        {key: 'text_changeCase', component: 'mod_nextblocks'},
        {key: 'text_trim', component: 'mod_nextblocks'},
        {key: 'text_count', component: 'mod_nextblocks'},
        {key: 'text_replace', component: 'mod_nextblocks'},
        {key: 'text_reverse', component: 'mod_nextblocks'},

        // Lists Blocks
        {key: 'lists_create_with', component: 'mod_nextblocks'},
        {key: 'lists_repeat', component: 'mod_nextblocks'},
        {key: 'lists_length', component: 'mod_nextblocks'},
        {key: 'lists_isEmpty', component: 'mod_nextblocks'},
        {key: 'lists_indexOf', component: 'mod_nextblocks'},
        {key: 'lists_getIndex', component: 'mod_nextblocks'},
        {key: 'lists_setIndex', component: 'mod_nextblocks'},
        {key: 'lists_getSublist', component: 'mod_nextblocks'},
        {key: 'lists_split', component: 'mod_nextblocks'},
        {key: 'lists_sort', component: 'mod_nextblocks'},
        {key: 'lists_reverse', component: 'mod_nextblocks'},

        // I/O Blocks
        {key: 'text_print', component: 'mod_nextblocks'},
        {key: 'text_ask', component: 'mod_nextblocks'},

        // Variables Blocks
        {key: 'variables_get', component: 'mod_nextblocks'},
        {key: 'variables_set', component: 'mod_nextblocks'},

        // Functions Blocks
        {key: 'procedures_defnoreturn', component: 'mod_nextblocks'},
        {key: 'procedures_defreturn', component: 'mod_nextblocks'},
        {key: 'procedures_callnoreturn', component: 'mod_nextblocks'},
        {key: 'procedures_callreturn', component: 'mod_nextblocks'},
        {key: 'procedures_ifreturn', component: 'mod_nextblocks'},
    ];

    return {
        init: function() {
            return str.get_strings(stringIds).then(function(localizedStrings) {
                const strings = {};
                stringIds.forEach((item, index) => {
                    strings[item.key] = localizedStrings[index];
                });

                return {
                    // Logic
                    [strings['category_logic']]: {
                        'controls_if': strings['controls_if'],
                        'logic_compare': strings['logic_compare'],
                        'logic_negate'      : strings['logic_negate'],
                        'logic_operation'   : strings['logic_operation'],
                        'logic_boolean'     : strings['logic_boolean'],
                        'logic_null'        : strings['logic_null'],
                        'logic_ternary'     : strings['logic_ternary'],
                    },

                    // Loops
                    [strings['category_loops']]: {
                        'controls_repeat_ext': strings['controls_repeat_ext'],
                        'controls_whileUntil': strings['controls_whileUntil'],
                        'controls_for'             : strings['controls_for'],
                        'controls_forEach'         : strings['controls_forEach'],
                        'controls_flow_statements' : strings['controls_flow_statements'],
                    },

                    // Math
                    [strings['category_math']]: {
                        'math_number'         : strings['math_number'],
                        'math_arithmetic'     : strings['math_arithmetic'],
                        'math_single'         : strings['math_single'],
                        'math_trig'           : strings['math_trig'],
                        'math_constant'       : strings['math_constant'],
                        'math_number_property': strings['math_number_property'],
                        'math_round'          : strings['math_round'],
                        'math_on_list'        : strings['math_on_list'],
                        'math_modulo'         : strings['math_modulo'],
                        'math_constrain'      : strings['math_constrain'],
                        'math_random_int'     : strings['math_random_int'],
                        'math_random_float'   : strings['math_random_float'],
                        'math_atan2'          : strings['math_atan2'],
                        'text_to_number'      : strings['text_to_number'],
                    },

                    // Text
                    [strings['category_text']]: {
                        'text'              : strings['text'],
                        'text_multiline'    : strings['text_multiline'],
                        'text_join'         : strings['text_join'],
                        'text_append'       : strings['text_append'],
                        'text_length'       : strings['text_length'],
                        'text_isEmpty'      : strings['text_isEmpty'],
                        'text_indexOf'      : strings['text_indexOf'],
                        'text_charAt'       : strings['text_charAt'],
                        'text_getSubstring' : strings['text_getSubstring'],
                        'text_changeCase'   : strings['text_changeCase'],
                        'text_trim'         : strings['text_trim'],
                        'text_count'        : strings['text_count'],
                        'text_replace'      : strings['text_replace'],
                        'text_reverse'      : strings['text_reverse'],
                    },

                    // Lists
                    [strings['category_lists']]: {
                        'lists_create_with' : strings['lists_create_with'],
                        'lists_repeat'      : strings['lists_repeat'],
                        'lists_length'      : strings['lists_length'],
                        'lists_isEmpty'     : strings['lists_isEmpty'],
                        'lists_indexOf'     : strings['lists_indexOf'],
                        'lists_getIndex'    : strings['lists_getIndex'],
                        'lists_setIndex'    : strings['lists_setIndex'],
                        'lists_getSublist'  : strings['lists_getSublist'],
                        'lists_split'       : strings['lists_split'],
                        'lists_sort'        : strings['lists_sort'],
                        'lists_reverse'     : strings['lists_reverse'],
                    },

                    // Input/Output
                    [strings['category_io']]: {
                        'text_print'        : strings['text_print'],
                        'text_ask'          : strings['text_ask'],
                    },

                    // Variables
                    [strings['category_variables']]: {
                        'variables_get' : strings['variables_get'],
                        'variables_set' : strings['variables_set'],
                    },

                    // Functions
                    [strings['category_functions']]: {
                        'procedures_defnoreturn'  : strings['procedures_defnoreturn'],
                        'procedures_defreturn'    : strings['procedures_defreturn'],
                        'procedures_callnoreturn' : strings['procedures_callnoreturn'],
                        'procedures_callreturn'   : strings['procedures_callreturn'],
                        'procedures_ifreturn'     : strings['procedures_ifreturn'],
                    },
                };
            });
        }
    };
});