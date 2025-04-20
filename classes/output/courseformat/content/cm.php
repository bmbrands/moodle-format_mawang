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

use core_courseformat\base as course_format;
use section_info;
use cm_info;
use renderer_base;
use stdClass;
/**
 * Class cm
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cm extends \core_courseformat\output\local\content\cm {

    /** @var section_info the section object */
    private $section;

    /**
     * Template name
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(renderer_base $renderer): string {
        return 'format_mawang/local/content/cm';
    }

        /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module ionfo
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, array $displayoptions = []) {
        parent::__construct($format, $section, $mod, $displayoptions);
        $this->section = $section;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $PAGE;

        $mod = $this->mod;
        $displayoptions = $this->displayoptions;

        $data = (object)[
            'grouping' => $mod->get_grouping_label($displayoptions['textclasses']),
            'modname' => get_string('pluginname', 'mod_' . $mod->modname),
            'url' => $mod->url,
            'activityname' => $mod->get_formatted_name(),
            'textclasses' => $displayoptions['textclasses'],
            'classlist' => [],
            'cmid' => $mod->id,
            'editing' => $PAGE->user_is_editing(),
            'sectionnum' => $this->section->section,
            'cmbulk' => !$mod->get_delegated_section_info(),
            'duration' => '20'
        ];

        // Add partial data segments.
        $haspartials = [];
        $haspartials['cmname'] = $this->add_cm_name_data($data, $output);
        $haspartials['availability'] = $this->add_availability_data($data, $output);
        $haspartials['alternative'] = $this->add_alternative_content_data($data, $output);
        $haspartials['completion'] = $this->add_completion_data($data, $output);
        $haspartials['dates'] = $this->add_dates_data($data, $output);
        $haspartials['editor'] = $this->add_editor_data($data, $output);
        $haspartials['groupmode'] = $this->add_groupmode_data($data, $output);
        $haspartials['visibility'] = $this->add_visibility_data($data, $output);
        $this->add_actvitychooserbutton_data($data, $output);
        $this->add_format_data($data, $haspartials, $output);

        // Calculated fields.
        if (!empty($data->url)) {
            $data->hasurl = true;
        }
        return $data;
    }
}
