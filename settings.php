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
 * Settings for format_mawang
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use core_reportbuilder\external\columns\sort\get;
use format_mawang\constants;

if ($ADMIN->fulltree) {
    $url = new moodle_url('/admin/course/resetindentation.php', ['format' => 'mawang']);
    $link = html_writer::link($url, get_string('resetindentation', 'admin'));
    $settings->add(new admin_setting_configcheckbox(
        'format_mawang/indentation',
        new lang_string('indentation', 'format_topics'),
        new lang_string('indentation_help', 'format_topics').'<br />'.$link,
        1
    ));
    $settings->add(new admin_setting_configtext('format_mawang/maxsectiondepth',
        get_string('maxsectiondepth', 'format_mawang'),
        get_string('maxsectiondepthdesc', 'format_mawang'), 2, PARAM_INT, 7));

    $options = [
        constants::COURSEINDEX_FULL => get_string('courseindexfull', 'format_mawang'),
        constants::COURSEINDEX_SECTIONS => get_string('courseindexsections', 'format_mawang'),
        constants::COURSEINDEX_NONE => get_string('courseindexnone', 'format_mawang'),
    ];
    $settings->add(new admin_setting_configselect('format_mawang/courseindexdisplay',
        get_string('courseindexdisplay', 'format_mawang'),
        get_string('courseindexdisplaydesc', 'format_mawang'), 0, $options));

    $settings->add(new admin_setting_configcheckbox('format_mawang/cmbacklink',
        get_string('cmbacklink', 'format_mawang'),
        get_string('cmbacklinkdesc', 'format_mawang'), 0));

    $settings->add(new admin_setting_configcheckbox('format_mawang/courseindexautoclose',
        get_string('courseindexautoclose', 'format_mawang'),
        get_string('courseindexautoclosedesc', 'format_mawang'), 1));

    // Get a list of all available modules.
    $modulelist = core_component::get_plugin_list('mod');
    $modules = [];
    foreach ($modulelist as $module => $dir) {
        $modules[$module] = get_string('modulename', $module) . ' (' . $module . ')';
    }

    $settings->add(new admin_setting_configmultiselect('format_mawang/autoblockopen',
        get_string('autoblockopen', 'format_mawang'),
        get_string('autoblockopendesc', 'format_mawang'),
        [], $modules));

    $settings->add(new admin_setting_configstoredfile('format_mawang/defaultsectionimage',
        get_string('defaultsectionimage', 'format_mawang'),
        get_string('defaultsectionimagedesc', 'format_mawang'), 'defaultsectionimage', 0,
        ['maxfiles' => 1, 'accepted_types' => ['.jpg', '.png', '.gif', '.svg', '.jpeg']]));
}
