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

namespace format_mawang\output\courseformat\content;

use stdClass;
use tool_monitor\output\managesubs\subs;
use renderer_base;

/**
 * Contains the section controls output class.
 *
 * @package   format_mawang
 * @copyright 2025 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section extends \core_courseformat\output\local\content\section {

    /** @var \format_mawang the course format */
    protected $format;

    /** @var int subsection level */
    protected $level = 1;

    /**
     * Template name
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_mawang/local/content/section';
    }

        /**
         * Export this data so it can be used as the context for a mustache template.
         *
         * @param renderer_base $output typically, the renderer that's calling this function
         * @return stdClass data context for a mustache template
         */
    public function export_for_template(renderer_base $output): stdClass {
        global $USER, $PAGE;

        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;

        $summary = new $this->summaryclass($format, $section);
        $lastvisited = get_user_preferences('courseformat_' . $format->get_format() . '_' . $course->id . '_lastvisited', 0);

        $data = (object)[
            'num' => $section->section ?? '0',
            'id' => $section->id,
            'sectionreturnnum' => $format->get_sectionnum(),
            'insertafter' => false,
            'summary' => $summary->export_for_template($output),
            'highlightedlabel' => $format->get_section_highlighted_name(),
            'sitehome' => $course->id == SITEID,
            'editing' => $PAGE->user_is_editing(),
            'displayonesection' => ($course->id != SITEID && $format->get_sectionid() == $section->id),
            // Section name is used as data attribute is to facilitate behat locators.
            'sectionname' => $format->get_section_name($section),
            'sectionimage' => $this->format->get_section_image($this->section->id),
            'courseid' => $course->id,
            'lastvisited' => $lastvisited == $section->section,
        ];

        $haspartials = [];
        $haspartials['availability'] = $this->add_availability_data($data, $output);
        $haspartials['visibility'] = $this->add_visibility_data($data, $output);
        $haspartials['editor'] = $this->add_editor_data($data, $output);
        $haspartials['header'] = $this->add_header_data($data, $output);
        $haspartials['cm'] = $this->add_cm_data($data, $output);
        $this->add_format_data($data, $haspartials, $output);

        return $data;
    }

    /**
     * Add the section cm list to the data structure.
     *
     * @param stdClass $data the current cm data reference
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return bool if the cm has name data
     */
    protected function add_cm_data(stdClass &$data, renderer_base $output): bool {
        $result = false;

        $section = $this->section;
        $format = $this->format;

        $display = $format->get_course_display();

        $showsummary = (
            $format->get_course_display() == COURSE_DISPLAY_MULTIPAGE
        );

        $showcmlist = $section->uservisible;

        // Add activities summary if necessary.
        if ($showsummary) {
            $cmsummary = new $this->cmsummaryclass($format, $section);
            $data->cmsummary = $cmsummary->export_for_template($output);
            $data->onlysummary = true;
            $result = true;
        }
        // Add the cm list.
        if ($showcmlist) {
            $cmlist = new $this->cmlistclass($format, $section);
            $data->cmlist = $cmlist->export_for_template($output);
            $result = true;
        }
        return $result;
    }

}
