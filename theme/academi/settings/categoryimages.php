<?php
// Category images settings for frontpage tiles.
// @package   theme_academi
// @copyright ...

defined('MOODLE_INTERNAL') || die();

$categoryimagesettings = new admin_settingpage(
    'theme_academi_categoryimages',
    get_string('categoryimages', 'theme_academi')
);

// Get top-level categories.
require_once($CFG->dirroot . '/course/lib.php');
$topcategory = core_course_category::top();
$categories = $topcategory->get_children();

// Add an image upload setting for each category.
if (!empty($categories)) {
    foreach ($categories as $category) {
        $name = 'theme_academi/categoryimage_' . $category->id;
        $title = get_string('categoryimage', 'theme_academi', $category->get_formatted_name());
        $description = get_string('categoryimage_desc', 'theme_academi', $category->get_formatted_name());
        $setting = new admin_setting_configstoredfile_autoresize(
            $name,
            $title,
            $description,
            'categoryimage_' . $category->id,
	    273,
	    180
        );
        $categoryimagesettings->add($setting);
    }
}

$settings->add($categoryimagesettings);

