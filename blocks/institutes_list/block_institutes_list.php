<?php
// This file is part of Moodle - http://moodle.org/.
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
 * Course list block.
 *
 * @package    block_institutes_list
 * @copyright  1999 onwards Martin Dougiamas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

class block_institutes_list extends block_list {

    public function init() {
        $this->title = get_string('pluginname', 'block_institutes_list');
    }

    public function has_config() {
        return true;
    }


public function get_content() {
    global $CFG, $USER, $DB, $OUTPUT, $PAGE;

    if ($this->content !== null) {
        return $this->content;
    }

    $this->content = new stdClass();
    $this->content->items  = [];
    $this->content->icons  = [];
    $this->content->footer = '';

    $icon = $OUTPUT->pix_icon('i/course', get_string('course'));

    // ---------------------------------------------------------------------
    // ALWAYS show all categories as tiles. Never show "My courses".
    // ---------------------------------------------------------------------

    // Still allow remote MNET courses if used.
    $this->get_remote_courses();

    // Load top-level categories.
    $topcategory = core_course_category::top();
    $categories = $topcategory->get_children();

    // Get Moodle course renderer to produce tiles.
    $courserenderer = $PAGE->get_renderer('core', 'course');
    $tileshtml = '';

    if (method_exists($courserenderer, 'frontpage_categories_list')) {
        $tileshtml = $courserenderer->frontpage_categories_list();
    }

    if (!empty($tileshtml)) {

        // Remove stretched-link so text centers.
        $tileshtml = str_replace('stretched-link ', '', $tileshtml);
        $tileshtml = str_replace('stretched-link', '', $tileshtml);

        // Insert tiles into block.
        $this->content->items[] = \html_writer::div(
            $tileshtml,
            'courselist-tiles-wrapper'
        );

        // Custom styling for tiles inside the block.
        $this->content->footer .= '
<style>
    .block_institutes_list .courselist-tiles-wrapper .category-tiles-container {
        gap: 0.75rem !important;
        justify-content: center;
    }

    .block_institutes_list .courselist-tiles-wrapper .category-tile {
        width: 165px !important;
    }

    .block_institutes_list .courselist-tiles-wrapper .card-title {
        font-size: 0.9rem;
        margin-bottom: .25rem;
        text-align: center;
        width: 100%;
    }

    .block_institutes_list .courselist-tiles-wrapper .card-title a {
        display: inline-block;
        text-align: center;
        width: 100%;
        margin-left: 15px;
    }

    .block_institutes_list .courselist-tiles-wrapper .card-body p {
        margin: 0;
        font-size: 0.75rem;
        text-align: center;
    }

    .block_institutes_list .courselist-tiles-wrapper .card-body {
        padding: .35rem .25rem;
    }

    .block_institutes_list .courselist-tiles-wrapper .category-image img {
        max-height: 110px;
        object-fit: cover;
        width: 100%;
    }
</style>
';

    } else {

        // Fallback if theme does not support tiles (rare).
        foreach ($categories as $category) {
            $categoryname = $category->get_formatted_name();
            $linkcss = $category->visible ? '' : ' class="dimmed" ';

            $this->content->items[] =
                '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/course/index.php?categoryid=' .
                $category->id . '">' . $icon . $categoryname . '</a>';
        }
    }

    // Add link to all courses.
    $this->content->footer .= '<a href="' . $CFG->wwwroot . '/course/index.php">' .
        get_string('fulllistofcourses') . '</a> ...';

    // Block title
    $this->title = get_string('categories');

    return $this->content;
}
    public function get_remote_courses() {
        global $CFG, $USER, $OUTPUT;

        if (!is_enabled_auth('mnet')) {
            // no need to query anything remote related
            return;
        }

        $icon = $OUTPUT->pix_icon('i/mnethost', get_string('host', 'mnet'));

        // shortcut - the rest is only for logged in users!
        if (!isloggedin() || isguestuser()) {
            return false;
        }

        if ($courses = get_my_remotecourses()) {
            $this->content->items[] = get_string('remotecourses', 'mnet');
            $this->content->icons[] = '';
            foreach ($courses as $course) {
                $this->content->items[] = '<a title="' .
                    format_string($course->shortname, true) . '" ' .
                    'href="' . $CFG->wwwroot . '/auth/mnet/jump.php?hostid=' .
                    $course->hostid . '&amp;wantsurl=/course/view.php?id=' .
                    $course->remoteid . '">' . $icon .
                    format_string(get_course_display_name_for_list($course)) . '</a>';
            }
            return true;
        }

        if ($hosts = get_my_remotehosts()) {
            $this->content->items[] = get_string('remotehosts', 'mnet');
            $this->content->icons[] = '';
            foreach ($USER->mnet_foreign_host_array as $somehost) {
                $this->content->items[] = $somehost['count'] .
                    get_string('courseson', 'mnet') . '<a title="' .
                    $somehost['name'] . '" href="' . $somehost['url'] . '">' .
                    $icon . $somehost['name'] . '</a>';
            }
            return true;
        }

        return false;
    }

    /**
     * Returns the role that best describes the course list block.
     *
     * @return string
     */
    public function get_aria_role() {
        return 'navigation';
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        global $CFG;

        $configs = (object)[
            'adminview' => $CFG->block_institutes_list_adminview,
            'hideallcourseslink' => $CFG->block_institutes_list_hideallcourseslink
        ];

        return (object)[
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }
}

