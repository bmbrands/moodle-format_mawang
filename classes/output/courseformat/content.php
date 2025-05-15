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

namespace format_mawang\output\courseformat;

use core_courseformat\external\get_state;
use course_modinfo;
use navigation_node;
use stdClass;

/**
 * Render a course content.
 *
 * @package   format_mawang
 * @copyright 2025 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends \core_courseformat\output\local\content {

    /** @var \format_mawang the course format class */
    protected $format;

    /** @var bool Flexsections format has add section. */
    protected $hasaddsection = true;

    /**
     * Template name for this exporter
     *
     * @param \renderer_base $renderer
     * @return string
     */
    public function get_template_name(\renderer_base $renderer): string {
        return 'format_mawang/local/content';
    }

    /**
     * Export this data so it can be used as the context for a mustache template (core/inplace_editable).
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return \stdClass data context for a mustache template
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;
        $format = $this->format;

        $tab = optional_param('tab', '', PARAM_INT);
        if ($tab) {
            $data = (object)[
                'tabcontent' => $format->get_customfields_tab_content($tab),
            ];
            $data->cssurl = new \moodle_url('/course/format/mawang/scss/styles.css', ['cache' => time()]);
            return $data;
        }

        $sections = $this->export_sections($output);
        $initialsection = '';


        $coursedisplay = $format->get_course_display();

        $data = (object)[
            'title' => $format->page_title(), // This method should be in the course_format class.
            'initialsection' => $initialsection,
            'sections' => $sections,
            'format' => $format->get_format(),
            'sectionreturn' => null,
            'teacherprofile' => $this->get_teacher_profile(),
            'multipage' => ($coursedisplay == COURSE_DISPLAY_MULTIPAGE),
        ];

        // The single section format has extra navigation.
        if ($this->format->get_sectionid()) {
            $singlesectionnum = $this->format->get_sectionnum();
            //if (!$PAGE->theme->usescourseindex) {
                $sectionnavigation = new $this->sectionnavigationclass($format, $singlesectionnum);
                $data->sectionnavigation = $sectionnavigation->export_for_template($output);

                $sectionselector = new $this->sectionselectorclass($format, $sectionnavigation);
                $data->sectionselector = $sectionselector->export_for_template($output);
            //}
            $data->hasnavigation = true;
            $data->singlesection = array_shift($data->sections);
            $data->sectionreturn = $singlesectionnum;
        }

        if ($this->hasaddsection) {
            $addsection = new $this->addsectionclass($format);
            $data->numsections = $addsection->export_for_template($output);
        }

        if ($format->show_editor()) {
            $bulkedittools = new $this->bulkedittoolsclass($format);
            $data->bulkedittools = $bulkedittools->export_for_template($output);
        }
        $data->cssurl = new \moodle_url('/course/format/mawang/scss/styles.css', ['cache' => time()]);
        $data->editing = $PAGE->user_is_editing();
        return $data;
    }

    /**
     * Get the teacher profile to display on the course page.
     *
     * @return string
     */
    public function get_teacher_profile() {
        global $OUTPUT;

        $coursectx = $this->format->get_context();
        if (!$coursectx) {
            return '';
        }

        // Teachers
        $teachers = get_enrolled_users($coursectx, 'moodle/course:changefullname');

        // Get the first teacher.
        $teacher = reset($teachers);
        $fields = (array)profile_user_record($teacher->id);
        return $OUTPUT->render_from_template(
            'format_mawang/teacherinfo',
            [
                'fullname' => fullname($teacher),
                'profileurl' => new \moodle_url('/user/profile.php', ['id' => $teacher->id]),
                'picture' => $OUTPUT->user_picture($teacher, ['size' => 100]),
                'teacher' => $teacher,
                'fields' => $fields,
            ]
        );
    }

    /**
     * We use our own version of the Boost secondary navigation
     */
    protected function secondary_navigation() {
        global $PAGE, $OUTPUT;

        $handler = \core_course\customfield\course_handler::create();
        $datas = $handler->get_instance_data($PAGE->course->id);
        $categories = [];
        foreach ($datas as $data) {
            $catid = $data->get_field()->get_category()->get('id');
            if (in_array($catid, $categories)) {
                continue;
            }
            $catname = $data->get_field()->get_category()->get('name');
            $nodeproperties = [
                'text' => $catname,
                'shorttext' => urlencode($catname),
                'key' => $catid,
                'type' => 'navigation_node::TYPE_COURSE',
                'action' => new \moodle_url('/course/view.php', ['id' => $PAGE->course->id, 'tab' => $catid]),
            ];
            $node = new navigation_node($nodeproperties);
            $PAGE->secondarynav->add_node($node);
            $categories[] = $catid;
        }

        $tablistnav = $PAGE->has_tablist_secondary_navigation();
        $moremenu = new \core\navigation\output\more_menu($PAGE->secondarynav, 'nav-tabs', true, $tablistnav);
        return $moremenu->export_for_template($OUTPUT);
    }
}
