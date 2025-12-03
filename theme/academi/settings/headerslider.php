<?php
defined('MOODLE_INTERNAL') || die();

// Upewnij się, że klasa jest załadowana - dopasuj ścieżkę jeśli potrzebna
require_once(__DIR__ . '/../lib.php'); // zakładam theme/academi/lib.php zawiera klasę

// Header Slider Settings
$headerslidersettings = new admin_settingpage('theme_academi_headerslider', get_string('headersliderheading', 'theme_academi'));

$headerslidersettings->add(new admin_setting_configcheckbox(
    'theme_academi/headersliderenabled',
    get_string('headersliderenabled', 'theme_academi'),
    get_string('headersliderenableddesc', 'theme_academi'),
    1
));

$headerslidersettings->add(new admin_setting_configtext(
    'theme_academi/headerslidernum',
    get_string('headerslidernum', 'theme_academi'),
    get_string('headerslidernumdesc', 'theme_academi'),
    3,
    PARAM_INT
));

// Tutaj używamy Twojej klasy admin_setting_configstoredfile_autoresize
// Docelowe wymiary: 1400 x 418
for ($i = 1; $i <= 5; $i++) {
    $headerslidersettings->add(new admin_setting_heading(
        "theme_academi/headerslide{$i}heading",
        get_string('headerslideheading', 'theme_academi', $i),
        ''
    ));

    // ZAMIANA: użyjemy klasy autoreize aby automatycznie skalować przy zapisie drafta
    $headerslidersettings->add(new admin_setting_configstoredfile_autoresize(
        "theme_academi/headerslide{$i}image",
        get_string('headerslideimage', 'theme_academi'),
        '',                     // opis (możesz dodać string jeśli chcesz)
        "headerslide{$i}image", // filearea (tak jak wcześniej)
        1400,                   // target width
        400                     // target height
    ));

    $headerslidersettings->add(new admin_setting_configtext(
        "theme_academi/headerslide{$i}link",
        get_string('headerslidelink', 'theme_academi'),
        get_string('headerslidelinkdesc', 'theme_academi'),
        '',
        PARAM_URL
    ));
}

$settings->add($headerslidersettings);

