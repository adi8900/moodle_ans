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

        $adminseesall = true;
        if (isset($CFG->block_institutes_list_adminview)) {
            if ($CFG->block_institutes_list_adminview == 'own') {
                $adminseesall = false;
            }
        }

        $allcourselink =
            (has_capability('moodle/course:update', context_system::instance())
                || empty($CFG->block_institutes_list_hideallcourseslink)) &&
            core_course_category::user_top();

        // ---------------------------------------------------------------------
        // 1. If user has their own courses, keep original "My courses" logic.
        // ---------------------------------------------------------------------
        if (empty($CFG->disablemycourses) && isloggedin() && !isguestuser() &&
            !(has_capability('moodle/course:update', context_system::instance()) && $adminseesall)) {

            if ($courses = enrol_get_my_courses()) {
                foreach ($courses as $course) {
                    $coursecontext = context_course::instance($course->id);
                    $linkcss = $course->visible ? '' : ' class="dimmed" ';

                    $this->content->items[] = '<a ' . $linkcss . ' title="' .
                        format_string($course->shortname, true, ['context' => $coursecontext]) . '" ' .
                        'href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' .
                        $icon . format_string(get_course_display_name_for_list($course)) . '</a>';
                }
                $this->title = get_string('mycourses');

                if ($allcourselink) {
                    $this->content->footer = '<a href="' . $CFG->wwwroot . '/course/index.php">' .
                        get_string('fulllistofcourses') . '</a> ...';
                }
            }
            $this->get_remote_courses();
            if ($this->content->items) {
                return $this->content;
            }
        }

        // ---------------------------------------------------------------------
        // 2. User is not enrolled in any courses.
        //    Use the same tiles renderer as the front page.
        // ---------------------------------------------------------------------
        $topcategory = core_course_category::top();
        if ($topcategory->is_uservisible() && ($categories = $topcategory->get_children())) {

            if (count($categories) > 1 || (count($categories) == 1 && $DB->count_records('course') > 200)) {

                // Ask the core course renderer (overridden by your theme) for tiles.
                $tileshtml = '';
                $courserenderer = $PAGE->get_renderer('core', 'course');

                if (method_exists($courserenderer, 'frontpage_categories_list')) {
                    $tileshtml = $courserenderer->frontpage_categories_list();
                }

                if (!empty($tileshtml)) {
                    // *** IMPORTANT FIX ***
                    // Remove "stretched-link" class only in this block,
                    // so the <a> text centers like the "liczba kursów" text.
                    $tileshtml = str_replace('stretched-link ', '', $tileshtml);
                    $tileshtml = str_replace('stretched-link', '', $tileshtml);

                    // Wrap tiles in a block-specific container for scoped CSS.
                    $this->content->items[] = \html_writer::div(
                        $tileshtml,
                        'courselist-tiles-wrapper'
                    );

                    // Block-only CSS (does NOT affect the main frontpage tiles).
                    $this->content->footer .= '
<style>
    /* Only affect tiles inside this block */
    .block_institutes_list .courselist-tiles-wrapper .category-tiles-container {
        gap: 0.75rem !important;
        justify-content: center;
    }

    /* Slightly smaller tiles for the block */
    .block_institutes_list .courselist-tiles-wrapper .category-tile {
        width: 165px !important;
    }

    /* h5 title wrapper: center content */
    .block_institutes_list .courselist-tiles-wrapper .card-title {
        font-size: 0.9rem;
        margin-bottom: .25rem;
        text-align: center;
        width: 100%;
    }

    /* Anchor behaves like normal centered text now (no stretched-link) */
    .block_institutes_list .courselist-tiles-wrapper .card-title a {
        display: inline-block;
        text-align: center;
        width: 100%;
margin-left: 15px;
    }

    /* Make the "number of courses" compact and centered */
    .block_institutes_list .courselist-tiles-wrapper .card-body p {
        margin: 0;
        font-size: 0.75rem;
        text-align: center;
    }

    /* Reduce vertical padding to make tiles less tall */
    .block_institutes_list .courselist-tiles-wrapper .card-body {
        padding: .35rem .25rem;
    }

    /* Make images consistent in block */
    .block_institutes_list .courselist-tiles-wrapper .category-image img {
        max-height: 110px;
        object-fit: cover;
        width: 100%;
    }
</style>
';
                } else {
                    // Fallback: original plain category links if tiles not available.
                    foreach ($categories as $category) {
                        $categoryname = $category->get_formatted_name();
                        $linkcss = $category->visible ? '' : ' class="dimmed" ';
                        $this->content->items[] = '<a ' . $linkcss . ' href="' .
                            $CFG->wwwroot . '/course/index.php?categoryid=' . $category->id . '">' .
                            $icon . $categoryname . '</a>';
                    }
                }

                if ($allcourselink) {
                    $this->content->footer .= '<a href="' . $CFG->wwwroot . '/course/index.php">' .
                        get_string('fulllistofcourses') . '</a> ...';
                }
                $this->title = get_string('categories');

            } else {
                // -----------------------------------------------------------------
                // Single-category case: keep default "list of courses" behavior.
                // -----------------------------------------------------------------
                $category = array_shift($categories);
                $courses = $category->get_courses();

                if ($courses) {
                    foreach ($courses as $course) {
                        $coursecontext = context_course::instance($course->id);
                        $linkcss = $course->visible ? '' : ' class="dimmed" ';

                        $this->content->items[] = '<a ' . $linkcss . ' title="' .
                            s($course->get_formatted_shortname()) . '" ' .
                            'href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '">' .
                            $icon . $course->get_formatted_name() . '</a>';
                    }

                    if ($allcourselink) {
                        $this->content->footer .= '<a href="' . $CFG->wwwroot . '/course/index.php">' .
                            get_string('fulllistofcourses') . '</a> ...';
                    }
                    $this->get_remote_courses();
                } else {
                    $this->content->icons[] = '';
                    $this->content->items[] = get_string('nocoursesyet');
                    if (has_capability('moodle/course:create', context_coursecat::instance($category->id))) {
                        $this->content->footer = '<a href="' . $CFG->wwwroot .
                            '/course/edit.php?category=' . $category->id . '">' .
                            get_string('addnewcourse') . '</a> ...';
                    }
                    $this->get_remote_courses();
                }
                $this->title = get_string('courses');
            }
        }

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

