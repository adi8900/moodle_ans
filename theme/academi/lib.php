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
 * lib.php
 * @package    theme_academi
 * @copyright  2015 onwards LMSACE Dev Team (http://www.lmsace.com)
 * @author    LMSACE Dev Team
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/adminlib.php');

define('FRONTPAGEPROMOTEDCOURSE', 10);
define('FRONTPAGESITEFEATURES', 11);
define('FRONTPAGEMARKETINGSPOT', 12);
define('FRONTPAGEJUMBOTRON', 13);

define('THEMEDEFAULT', 16);
define('SMALL', 15);
define('MEDIUM', 17);
define('LARGE', 18);

define('MOODLEBASED', 0);
define('THEMEBASED', 1);

define('CAROUSEL', 1);

define('EXPAND', 0);
define('COLLAPSE', 1);

define('NO', 0);
define('YES', 1);

define('SAMEWINDOW', 0);
define('NEWWINDOW', 1);

define('LOGO', 0);
define('SITENAME', 1);
define('LOGOANDSITENAME', 2);

/**
 * Load the Jquery and migration files
 * @param moodle_page $page
 * @return void
 */
function theme_academi_page_init(moodle_page $page) {
    global $CFG;
    $page->requires->js_call_amd('theme_academi/theme', 'init');
}

/**
 * Loads the CSS Styles and replace the background images.
 * If background image not available in the settings take the default images.
 *
 * @param string $css
 * @param object $theme
 * @return string
 */
function theme_academi_process_css($css, $theme) {
    global $OUTPUT, $CFG;
    $css = theme_academi_pre_css_set_fontwww($css);
    // Set custom CSS.
    $customcss = $theme->settings->customcss;
    $css = theme_academi_set_customcss($css , $customcss);
    return $css;
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 * @return string $css
 */
function theme_academi_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_academi_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    static $theme;
    $bgimgs = ['footerbgimg', 'loginbg', 'mspotmedia'];

    if (empty($theme)) {
        $theme = theme_config::load('academi');
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {

        if ($filearea === 'logo') {
            return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
        } else if ($filearea === 'footerlogo') {
            return $theme->setting_file_serve('footerlogo', $args, $forcedownload, $options);
        } else if ($filearea === 'pagebackground') {
            return $theme->setting_file_serve('pagebackground', $args, $forcedownload, $options);
        } else if (preg_match("/slide[1-9][0-9]*image/", $filearea) !== false) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (in_array($filearea, $bgimgs)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * Loads the CSS Styles and put the font path
 *
 * @param string $css
 * @return string
 */
function theme_academi_pre_css_set_fontwww($css) {
    global $CFG;
    if (empty($CFG->themewww)) {
        $themewww = $CFG->wwwroot."/theme";
    } else {
        $themewww = $CFG->themewww;
    }
    $tag = '[[setting:fontwww]]';
    $css = str_replace($tag, $themewww.'/academi/fonts/', $css);
    return $css;
}

/**
 * Load the font folder path into the scss.
 * @return string
 */
function theme_academi_set_fontwww() {
    global $CFG;
    if (empty($CFG->themewww)) {
        $themewww = $CFG->wwwroot."/theme";
    } else {
        $themewww = $CFG->themewww;
    }
    $fontwww = '$fontwww: "'.$themewww.'/academi/fonts/"'.";\n";
    return $fontwww;
}


/**
 * Description
 *
 * @param string $type logo position type.
 * @return type|string
 */
function theme_academi_get_logo_url($type = 'header') {
    global $OUTPUT;
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('academi');
    }
    if ($type == 'header') {
        $logo = $theme->setting_file_url('logo', 'logo');
        $logo = empty($logo) ? $OUTPUT->get_compact_logo_url() : $logo;
    } else if ($type == 'footer') {
        $logo = $theme->setting_file_url('footerlogo', 'footerlogo');
        $logo = empty($logo) ? '' : $logo;
    }
    return $logo;
}

/**
 *
 * Description
 * @param string $setting
 * @param bool $format
 * @return string
 */
function theme_academi_get_setting($setting, $format = true) {
    global $CFG, $PAGE;
    require_once($CFG->dirroot . '/lib/weblib.php');
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('academi');
    }
    if (empty($theme->settings->$setting)) {
        return false;
    } else if (!$format) {
        $return = $theme->settings->$setting;
    } else if ($format === 'format_text') {
        $return = format_text($theme->settings->$setting, FORMAT_PLAIN);
    } else if ($format === 'format_html') {
        $return = format_text($theme->settings->$setting, FORMAT_HTML, ['trusted' => true, 'noclean' => true]);
    } else if ($format === 'file') {
        $return = $PAGE->theme->setting_file_url($setting, $setting);
    } else {
        $return = format_string($theme->settings->$setting);
    }
    return (isset($return)) ? theme_academi_lang($return) : '';
}

/**
 * Returns the language values from the given lang string or key.
 * @param string $key
 * @return string
 */
function theme_academi_lang($key='') {
    $pos = strpos($key, 'lang:');
    if ($pos !== false) {
        list($l, $k) = explode(":", $key);
        if (get_string_manager()->string_exists($k, 'theme_academi')) {
            $v = get_string($k, 'theme_academi');
            return $v;
        } else {
            return $key;
        }
    } else {
        return $key;
    }
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_academi_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = (isset($theme->settings->preset) && !empty($theme->settings->preset)) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = \context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/academi/scss/preset/default.scss');
    } else if ($filename == 'eguru') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/academi/scss/preset/eguru.scss');
    } else if ($filename == 'klass') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/academi/scss/preset/klass.scss');
    } else if ($filename == 'enlightlite') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/academi/scss/preset/enlightlite.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_academi', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Fallback to default.
        $scss .= file_get_contents($CFG->dirroot . '/theme/academi/scss/preset/default.scss');
    }
    return $scss;
}

/**
 * Get the configuration values into main scss variables.
 *
 * @param string $theme theme data.
 * @return string $scss return the scss values.
 */
function theme_academi_get_pre_scss($theme) {
    $scss = '';
    $helperobj = new theme_academi\helper();
    $scss .= $helperobj->load_bgimages($theme, $scss);
    $scss .= $helperobj->load_additional_scss_settings();
    return $scss;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_academi_get_extra_scss($theme) {
    // Load the settings from the parent.
    $theme = theme_config::load('boost');
    // Call the parent themes get_extra_scss function.
    return theme_boost_get_extra_scss($theme);
}

class admin_setting_configstoredfile_autoresize extends admin_setting_configstoredfile {

    protected $targetwidth;
    protected $targetheight;

    public function __construct($name, $visiblename, $description, $filearea, $targetwidth, $targetheight, $options = []) {
        parent::__construct($name, $visiblename, $description, $filearea, 0, $options);
        $this->targetwidth = (int)$targetwidth;
        $this->targetheight = (int)$targetheight;
    }

    protected function safe_create_from_string($data) {
        $im = @imagecreatefromstring($data);
        return $im ?: false;
    }

    public function save($data) {
        global $CFG, $USER;
        $fs = get_file_storage();

        // Dla draftów MUSI być context_user, nie system.
        $usercontext = \context_user::instance($USER->id);
        $draftid = $data;

        if (!$draftid) {
            return parent::save($data);
        }

        // Check GD availability early
        if (!function_exists('imagecreatetruecolor')) {
            error_log("autoresize: GD library not available - aborting resize");
            \core\notification::add('Autoresize: brak biblioteki GD na serwerze — operacja anulowana.', \core\output\notification::NOTIFY_ERROR);
            return parent::save($data);
        }

        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftid, 'id', false);
        error_log('autoresize: found ' . count($files) . ' draft files for draftid=' . $draftid . ' in user context ' . $usercontext->id);

        foreach ($files as $file) {
            // log original metadata for debugging
            error_log("autoresize: processing fileid={$file->get_id()} filename={$file->get_filename()} component={$file->get_component()} filearea={$file->get_filearea()} contextid={$file->get_contextid()} itemid={$file->get_itemid()} filepath={$file->get_filepath()}");

            $temp = $file->copy_content_to_temp();
            if (!$temp || !file_exists($temp)) {
                error_log('autoresize: could not copy temp for fileid ' . $file->get_id());
                \core\notification::add('Autoresize: nie udało się utworzyć pliku tymczasowego dla ' . $file->get_filename(), \core\output\notification::NOTIFY_ERROR);
                continue;
            }

            $imageinfo = @getimagesize($temp);
            if ($imageinfo === false) {
                @unlink($temp);
                error_log('autoresize: getimagesize failed for temp ' . $temp . ' (filename=' . $file->get_filename() . ')');
                \core\notification::add('Autoresize: nie można odczytać informacji o obrazie dla ' . $file->get_filename(), \core\output\notification::NOTIFY_ERROR);
                continue;
            }

            list($width, $height, $type) = $imageinfo;

            // Wczytaj źródło (bezpiecznie)
            $src = false;
            try {
                switch ($type) {
                    case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($temp); break;
                    case IMAGETYPE_PNG:  $src = @imagecreatefrompng($temp); break;
                    case IMAGETYPE_GIF:  $src = @imagecreatefromgif($temp); break;
                    default: $src = false; break;
                }
            } catch (Throwable $e) {
                $src = false;
            }

            if (!$src) {
                $content = @file_get_contents($temp);
                if ($content !== false) { $src = $this->safe_create_from_string($content); }
            }

            if (!$src) {
                error_log('autoresize: could not create image resource for file ' . $file->get_filename());
                @unlink($temp);
                \core\notification::add('Autoresize: nie udało się utworzyć zasobu obrazu dla ' . $file->get_filename(), \core\output\notification::NOTIFY_ERROR);
                continue;
            }

            // EXIF rotation (JPEG)
            if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                try {
                    $exif = @exif_read_data($temp);
                    if (!empty($exif['Orientation'])) {
                        $orientation = (int)$exif['Orientation'];
                        if ($orientation > 1) {
                            $rotated = null;
                            if ($orientation == 3) {
                                $rotated = imagerotate($src, 180, 0);
                            } else if ($orientation == 6) {
                                $rotated = imagerotate($src, -90, 0);
                            } else if ($orientation == 8) {
                                $rotated = imagerotate($src, 90, 0);
                            }
                            if ($rotated) {
                                imagedestroy($src);
                                $src = $rotated;
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log('autoresize: exif_read_data failed for ' . $file->get_filename() . ' - ' . $e->getMessage());
                }
            }

            $src_w = imagesx($src);
            $src_h = imagesy($src);

            error_log("autoresize: original dims for {$file->get_filename()} = {$src_w}x{$src_h}, target={$this->targetwidth}x{$this->targetheight}");

            $saved = false;

            if ($src_w === $this->targetwidth && $src_h === $this->targetheight) {
                // identyczne wymiary — po prostu zapisz ponownie w tym samym formacie
                try {
                    if ($type === IMAGETYPE_JPEG) {
                        $saved = imagejpeg($src, $temp, 90);
                    } else if ($type === IMAGETYPE_PNG) {
                        $saved = imagepng($src, $temp, 6);
                    } else if ($type === IMAGETYPE_GIF) {
                        $saved = imagegif($src, $temp);
                    }
                } catch (Throwable $e) {
                    $saved = false;
                }
                imagedestroy($src);
                if (!$saved) {
                    @unlink($temp);
                    error_log('autoresize: failed to save identical-dim image for '.$file->get_filename());
                    \core\notification::add('Autoresize: nie udało się zapisać pliku ' . $file->get_filename(), \core\output\notification::NOTIFY_ERROR);
                    continue;
                } else {
                    error_log('autoresize: saved identical-dim image for '.$file->get_filename());
                    \core\notification::add('Plik ' . $file->get_filename() . ' ma już docelowe wymiary (' . $this->targetwidth . '×' . $this->targetheight . '), zapisano ponownie.', \core\output\notification::NOTIFY_SUCCESS);
                }
            } else {
                $scale = min($this->targetwidth / $src_w, $this->targetheight / $src_h);
                $new_w = max(1, (int) round($src_w * $scale));
                $new_h = max(1, (int) round($src_h * $scale));

                error_log("autoresize: scale={$scale} new={$new_w}x{$new_h} for {$file->get_filename()}");

                $inter = imagecreatetruecolor($new_w, $new_h);
                if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                    imagealphablending($inter, false);
                    imagesavealpha($inter, true);
                    $transparent = imagecolorallocatealpha($inter, 0,0,0,127);
                    imagefilledrectangle($inter,0,0,$new_w,$new_h,$transparent);
                } else {
                    $white = imagecolorallocate($inter,255,255,255);
                    imagefilledrectangle($inter,0,0,$new_w,$new_h,$white);
                }

                $res1 = @imagecopyresampled($inter, $src, 0,0,0,0, $new_w, $new_h, $src_w, $src_h);

                $newimage = imagecreatetruecolor($this->targetwidth, $this->targetheight);
                if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                    imagealphablending($newimage, false);
                    imagesavealpha($newimage, true);
                    $transparent = imagecolorallocatealpha($newimage,0,0,0,127);
                    imagefilledrectangle($newimage,0,0,$this->targetwidth,$this->targetheight,$transparent);
                } else {
                    $white = imagecolorallocate($newimage,255,255,255);
                    imagefilledrectangle($newimage,0,0,$this->targetwidth,$this->targetheight,$white);
                }

                $dst_x = (int) round(($this->targetwidth - $new_w) / 2);
                $dst_y = (int) round(($this->targetheight - $new_h) / 2);
                $res2 = @imagecopy($newimage, $inter, $dst_x, $dst_y, 0,0, $new_w, $new_h);

                // Dodatkowa obsługa transparentności dla GIF - zachowanie przezroczystego indeksu
                if ($type === IMAGETYPE_GIF) {
                    $transparent_index = imagecolortransparent($src);
                    if ($transparent_index >= 0) {
                        $rgb = imagecolorsforindex($src, $transparent_index);
                        $transparent_new = imagecolorallocate($newimage, $rgb['red'], $rgb['green'], $rgb['blue']);
                        imagefill($newimage, 0, 0, $transparent_new);
                        imagecolortransparent($newimage, $transparent_new);
                    }
                }

                $saved = false;
                try {
                    if ($res1 && $res2) {
                        if ($type === IMAGETYPE_JPEG) {
                            $saved = imagejpeg($newimage, $temp, 90);
                        } else if ($type === IMAGETYPE_PNG) {
                            $saved = imagepng($newimage, $temp, 6);
                        } else if ($type === IMAGETYPE_GIF) {
                            $saved = imagegif($newimage, $temp);
                        }
                    } else {
                        error_log('autoresize: resampling failed for '.$file->get_filename().' res1='.(int)$res1.' res2='.(int)$res2);
                    }
                } catch (Throwable $e) {
                    $saved = false;
                    error_log('autoresize: exception while saving resized image for '.$file->get_filename().' - '.$e->getMessage());
                }

                imagedestroy($inter);
                imagedestroy($newimage);
                imagedestroy($src);

                if (!$saved) {
                    @unlink($temp);
                    error_log('autoresize: failed to save resized image for '.$file->get_filename());
                    \core\notification::add('Autoresize: nie udało się zapisać przeskalowanego obrazu dla ' . $file->get_filename(), \core\output\notification::NOTIFY_ERROR);
                    continue;
                } else {
                    error_log('autoresize: saved resized image for '.$file->get_filename());
                    \core\notification::add('Plik ' . $file->get_filename() . ' został przeskalowany do ' . $this->targetwidth . '×' . $this->targetheight . '.', \core\output\notification::NOTIFY_SUCCESS);
                }
            }

            // Usuń stary rekord pliku (jeżeli istnieje) i utwórz nowy z tymi samymi metadanymi
            try {
                $file->delete();
            } catch (Exception $e) {
                error_log('autoresize: unable to delete original file id='.$file->get_id().' - '.$e->getMessage());
            }

            $filerecord = [
                'contextid' => $file->get_contextid(),
                'component' => $file->get_component(),
                'filearea'  => $file->get_filearea(),
                'itemid'    => $file->get_itemid(),
                'filepath'  => $file->get_filepath(),
                'filename'  => $file->get_filename(),
            ];
            try {
                $fs->create_file_from_pathname($filerecord, $temp);
                error_log('autoresize: create_file_from_pathname succeeded for '.$file->get_filename());
            } catch (Exception $e) {
                error_log('autoresize: create_file_from_pathname failed for '.$file->get_filename().' - '.$e->getMessage());
                \core\notification::add('Autoresize: błąd przy tworzeniu pliku w repozytorium dla ' . $file->get_filename() . ' - ' . $e->getMessage(), \core\output\notification::NOTIFY_ERROR);
            }

            @unlink($temp);
        }

        return parent::save($data);
    }
}

