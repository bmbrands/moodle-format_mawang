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

namespace format_mawang\output\courseformat\content\section;

use renderer_base;
use stdClass;

/**
 * Class cmitem
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmitem extends \core_courseformat\output\local\content\section\cmitem {
    public function export_for_template(\renderer_base $output): \stdClass {
        $format = $this->format;
        $course = $format->get_course();
        $mod = $this->mod;

        $data = new stdClass();
        $data->cms = [];

        $completionenabled = $course->enablecompletion == COMPLETION_ENABLED;
        $showactivityconditions = $completionenabled && $course->showcompletionconditions == COMPLETION_SHOW_CONDITIONS;
        $showactivitydates = !empty($course->showactivitydates);

        // This will apply styles to the course homepage when the activity information output component is displayed.
        $hasinfo = $showactivityconditions || $showactivitydates;

        $item = new $this->cmclass($format, $this->section, $mod, $this->displayoptions);
        return (object)[
            'id' => $mod->id,
            'anchor' => "module-{$mod->id}",
            'module' => $mod->modname,
            'extraclasses' => $mod->extraclasses,
            'cmformat' => $item->export_for_template($output),
            'hasinfo' => $hasinfo,
            'indent' => ($format->uses_indentation()) ? $mod->indent : 0,
            'groupmode' => $mod->groupmode,
            'duration' => 10,
        ];
    }

    public function get_template_name(\renderer_base $renderer): string {
        return "format_mawang/local/content/section/cmitem";
    }
}
