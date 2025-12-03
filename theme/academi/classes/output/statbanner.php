<?php
namespace theme_academi\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

class statbanner implements renderable, templatable {

    public function export_for_template(renderer_base $output) {
        global $DB;

        // Users
        $totalusers = $DB->count_records_select('user',
            "deleted = 0 AND suspended = 0 AND confirmed = 1"
        ) - 1;

        // Courses
        $totalcourses = $DB->count_records_sql("
            SELECT COUNT(*)
              FROM {course}
             WHERE id <> 1
               AND visible = 1
        ");

        // Activities
        $totalactivities = $DB->count_records_sql("
            SELECT COUNT(*)
              FROM {course_modules} cm
              JOIN {course} c ON c.id = cm.course
             WHERE c.id <> 1
               AND c.visible = 1
               AND cm.visible = 1
        ");

        // Format numbers
        $fmt = fn(int $n) => number_format($n, 0, ',', ' ');

        return [
            'title_main' => 'WYBRANE',
            'title_gold' => 'LICZBY',

            'stats' => [
                [
                    'icon'  => 'fa-user-circle',
                    'value' => $fmt($totalusers),
                    'label' => get_string('users', 'moodle'),
                ],
                [
                    'icon'  => 'fa-graduation-cap',
                    'value' => $fmt($totalcourses),
                    'label' => get_string('courses'),
                ],
                [
                    'icon'  => 'fa-puzzle-piece',
                    'value' => $fmt($totalactivities),
                    'label' => get_string('activities'),
                ],
            ]
        ];
    }
}
