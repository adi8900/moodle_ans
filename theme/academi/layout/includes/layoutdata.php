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
 * Columns 2 Layout
 * @package    theme_academi
 * @copyright  2015 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @author    LMSACE Dev Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/behat/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(dirname(__FILE__) .'/themedata.php');
global $SESSION, $PAGE;

// Obsługa parametru darkmode=on/off
$darkmodeparam = optional_param('darkmode', null, PARAM_ALPHA);
if ($darkmodeparam === 'on') {
    if (isloggedin() && !isguestuser()) {
        set_user_preference('theme_academi_darkmode', 1);
    } else {
        $SESSION->theme_academi_darkmode = 1;
    }
} else if ($darkmodeparam === 'off') {
    if (isloggedin() && !isguestuser()) {
        set_user_preference('theme_academi_darkmode', 0);
    } else {
        unset($SESSION->theme_academi_darkmode);
    }
}

// Odczyt trybu
$darkmodeenabled = false;
if (isloggedin() && !isguestuser()) {
    $darkmodeenabled = (bool)get_user_preferences('theme_academi_darkmode', 0);
} else if (!empty($SESSION->theme_academi_darkmode)) {
    $darkmodeenabled = true;
}


// URL-e dla guzika
$darkmodeonurl  = new moodle_url($PAGE->url, ['darkmode' => 'on']);
$darkmodeoffurl = new moodle_url($PAGE->url, ['darkmode' => 'off']);

// Dodaj do $templatecontext dla każdego layoutu
$templatecontext['darkmodeenabled'] = $darkmodeenabled;
$templatecontext['darkmodeonurl'] = $darkmodeonurl->out(false);
$templatecontext['darkmodeoffurl'] = $darkmodeoffurl->out(false);
$preset = optional_param('preset', 0, PARAM_TEXT);
if (!empty($preset) && isset($preset)) {
    set_config('preset', $preset, 'theme_academi');
    // Purge the theme cache to show the old icons in the GUI.
    theme_reset_all_caches();
}

// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();
// Add block button in editing mode.
$addblockbutton = $OUTPUT->addblockbutton();


if (isloggedin()) {
    $courseindexopen = (get_user_preferences('drawer-open-index', true) == true);
    $blockdraweropen = (get_user_preferences('drawer-open-block') == true);
} else {
    $courseindexopen = false;
    $blockdraweropen = false;
}

if (defined('BEHAT_SITE_RUNNING')) {
    $blockdraweropen = true;
}
if (!isset($extraclasses) || !is_array($extraclasses)) {
    $extraclasses = [];
}
$extraclasses = ['uses-drawers'];
if ($courseindexopen) {
    $extraclasses[] = 'drawer-open-index';
}

$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = (strpos($blockshtml, 'data-block=') !== false || !empty($addblockbutton));
if (!$hasblocks) {
    $blockdraweropen = false;
}
$courseindex = core_course_drawer();
if (!$courseindex) {
    $courseindexopen = false;
}

$themestyleheader = theme_academi_get_setting('themestyleheader');
$extraclasses[] = ($themestyleheader) ? 'theme-based-header' : 'moodle-based-header';
if ($darkmodeenabled) {
    $extraclasses[] = 'dark-mode';
}
$forceblockdraweropen = $OUTPUT->firstview_fakeblocks();

$secondarynavigation = false;
$overflow = '';
if ($PAGE->has_secondary_navigation()) {
    $tablistnav = $PAGE->has_tablist_secondary_navigation();
    $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
    $secondarynavigation = $moremenu->export_for_template($OUTPUT);
    $overflowdata = $PAGE->secondarynav->get_overflow_menu_data();
    if (!is_null($overflowdata)) {
        $overflow = $overflowdata->export_for_template($OUTPUT);
    }
}

$primary = new core\navigation\output\primary($PAGE);
$renderer = $PAGE->get_renderer('core');
$primarymenu = $primary->export_for_template($renderer);
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions() && !$PAGE->has_secondary_navigation();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;

$header = $PAGE->activityheader;
$headercontent = $header->export_for_template($renderer);
$templatecontext += [
    'sitename' => format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'courseindexopen' => $courseindexopen,
    'blockdraweropen' => $blockdraweropen,
    'courseindex' => $courseindex,
    'primarymoremenu' => $primarymenu['moremenu'],
    'secondarymoremenu' => $secondarynavigation ?: false,
    'mobileprimarynav' => $primarymenu['mobileprimarynav'],
    'usermenu' => $primarymenu['user'],
    'langmenu' => $primarymenu['lang'],
    'forceblockdraweropen' => $forceblockdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'overflow' => $overflow,
    'headercontent' => $headercontent,
    'addblockbutton' => $addblockbutton,
];
/* --- POCZĄTEK: NADPISYWANIE LOGO DLA DARK MODE --- */

// Ten kod wykonuje się TYLKO jeśli wykryto tryb ciemny w liniach wyżej
if ($darkmodeenabled) {

    // 1. Definiujemy URL-e do ciemnych wersji logo
    $logo_dark_pl = $PAGE->theme->setting_file_url('logo_dark', 'logo_dark');
    $logo_dark_en = $PAGE->theme->setting_file_url('logo_en_dark', 'logo_en_dark');
    
    // 2. Sprawdzamy język
    $currentlang = current_language();
    $is_english = (strpos($currentlang, 'en') === 0);

    // 3. Logika wyboru
    if ($is_english) {
        // --- JĘZYK ANGIELSKI + DARK MODE ---
        if (!empty($logo_dark_en)) {
            // Mamy dedykowane angielskie ciemne
            $templatecontext['logourl'] = $logo_dark_en;
        } elseif (!empty($logo_dark_pl)) {
            // Nie ma angielskiego ciemnego, używamy polskiego ciemnego (lepsze to niż jasne)
            $templatecontext['logourl'] = $logo_dark_pl;
        }
        // Jeśli nie ma żadnego ciemnego, zostaje jasne angielskie (ustawione w themedata.php)
    } else {
        // --- JĘZYK POLSKI + DARK MODE ---
        if (!empty($logo_dark_pl)) {
            $templatecontext['logourl'] = $logo_dark_pl;
        }
        // Jeśli nie ma polskiego ciemnego, zostaje jasne polskie
    }
}
/* --- KONIEC: NADPISYWANIE LOGO DLA DARK MODE --- */
