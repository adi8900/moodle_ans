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
 *
 * @package     mod_nextblocks
 * @category    upgrade
 * @copyright   2025 Rui Correia<rjr.correia@campus.fct.unl.pt>
 * @copyright   based on work by 2024 Duarte Pereira<dg.pereira@campus.fct.unl.pt>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$services = [
    'mypluginservice' => [                      // The name of the web service.
        'functions' => ['mod_nextblocks_save_workspace', 'mod_nextblocks_submit_workspace', 'mod_nextblocks_submit_reaction',
            'mod_nextblocks_save_message', 'mod_nextblocks_get_messages'], // Web service functions of this service.
        'requiredcapability' => '',                // If set, the web service user need this capability to access.
        // Any function of this service. For example: 'some/capability:specified'.
        'restrictedusers' => 0,                      // If enabled, the Moodle administrator must link some user to this service.
        // Into the administration.
        'enabled' => 1,                               // If enabled, the service can be reachable on a default installation.
        'shortname' => 'nextblocksservice', // The short name used to refer to this service from elsewhere.
    ],
];

$functions = [
    'mod_nextblocks_save_workspace' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'save_workspace',
        'classpath' => 'mod/nextblocks/externallib.php',
        'description' => 'Save current workspace',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'mod_nextblocks_submit_workspace' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'submit_workspace',
        'classpath' => 'mod/nextblocks/externallib.php',
        'description' => 'Submit current workspace',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'mod_nextblocks_submit_reaction' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'submit_reaction',
        'classpath' => 'mod/nextblocks/externallib.php',
        'description' => 'Save reaction to exercise',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'mod_nextblocks_save_message' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'save_message',
        'classpath' => 'mod/nextblocks/externallib.php',
        'description' => 'Save message to database',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'mod_nextblocks_get_messages' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'get_messages',
        'classpath' => 'mod/nextblocks/externallib.php',
        'description' => 'Get last X messages from database',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
    ],

    'mod_nextblocks_get_comments' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'get_comments',
        'description' => 'Get comments for a block',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_nextblocks_save_comment' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'save_comment',
        'description' => 'Save a comment',
        'type' => 'write',
        'ajax' => true,
    ],
    'mod_nextblocks_get_all_block_comments' => [
        'classname' => 'mod_nextblocks_external',
        'methodname' => 'get_all_block_comments',
        'description' => 'Get all blocks with comments',
        'type' => 'read',
        'ajax' => true,
    ],
];
