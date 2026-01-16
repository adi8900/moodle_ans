<?php
defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/includes/layoutdata.php');
require_once($CFG->dirroot . '/theme/academi/classes/helper.php');
use theme_academi\helper;
$headerslider = \theme_academi\helper::get_headerslider();
$sliderconfig = [];
require_once(dirname(__FILE__) .'/includes/homeslider.php');
// Include CSS/JS for slider.
$PAGE->requires->jquery();
$PAGE->requires->css(new moodle_url('/theme/academi/style/slick.css'));
$PAGE->requires->js_call_amd('theme_academi/frontpage', 'init');
//$PAGE->requires->js(new moodle_url('/theme/academi/js/headerslider.js'), true);
$PAGE->requires->css(new moodle_url('/theme/academi/style/headerslider.css'));
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$course_renderer = $PAGE->get_renderer('theme_academi', 'core\course');
// Jumbotron class.
$jumbotronclass = (!empty(theme_academi_get_setting('jumbotronstatus'))) ? 'jumbotron-element' : '';
$statbanner = new \theme_academi\output\statbanner();
$templatecontext['frontpage_categories'] = $course_renderer->frontpage_categories_list();
// Add slider content to template context (only if enabled).
if (!empty($headerslider['enabled']) && !empty($headerslider['slides'])) {
    $templatecontext['headerslider'] =
        $OUTPUT->render_from_template('theme_academi/headerslider', $headerslider);
} else {
    $templatecontext['headerslider'] = '';
}
$course_renderer = $PAGE->get_renderer('theme_academi', 'core\course');
//$templatecontext['frontpage_stats_banner'] = $course_renderer->frontpage_stats_banner();
$templatecontext['frontpage_stats_banner'] =
    $OUTPUT->render_from_template('theme_academi/statbanner',
        $statbanner->export_for_template($OUTPUT));
// Add other context variables.
$templatecontext += $sliderconfig;
$templatecontext += [
    'bodyattributes' => $bodyattributes,
    'jumbotronclass' => $jumbotronclass,
];
echo $OUTPUT->render_from_template('theme_academi/frontpage', $templatecontext);


