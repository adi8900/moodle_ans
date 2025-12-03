<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/theme/academi/classes/helper.php");

/**
 * Build header slider data for Mustache template.
 */
function theme_academi_headerslider_data() {
    global $CFG;
    $data = [];
    $data['enabled'] = theme_academi_get_setting('headersliderenabled');
    $data['slides'] = [];

    if (empty($data['enabled'])) {
        return $data;
    }

    $numslides = (int) theme_academi_get_setting('headerslidernum');
    $helper = new \theme_academi\helper();

    for ($i = 1; $i <= $numslides; $i++) {
        $imgurl = $helper->render_slideimg_headerslide($i, "headerslide{$i}image");
        $link   = theme_academi_get_setting("headerslide{$i}link");

        // Convert moodle_url object to string (so Mustache can use it)
        if ($imgurl instanceof moodle_url) {
            $imgurl = $imgurl->out(false);
        }

        if (!empty($imgurl)) {
            $data['slides'][] = [
                'image' => $imgurl,
                'link'  => $link
            ];
        }
    }

    return $data;
}

$headerslider = theme_academi_headerslider_data();
