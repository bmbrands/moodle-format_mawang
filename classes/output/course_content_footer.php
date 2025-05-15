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

namespace format_mawang\output;
use moodle_url;

/**
 * Class course_content_footer
 *
 * @package    format_mawang
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_content_footer implements \renderable, \templatable {

    /**
     * @var \action_link $prevlink The action link object for the prev link.
     */
    public $prevlink = null;

    /**
     * @var \action_link $nextlink The action link object for the next link.
     */
    public $nextlink = null;

    /**
     * course_content_footer constructor.
     *
     */
    public function __construct() {
        global $PAGE;
        // First we should check if we want to add navigation.
        $context = $PAGE->context;
        if (($PAGE->pagelayout !== 'incourse' && $PAGE->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($PAGE->cm->is_stealth()) {
            return '';
        }

        $course = $PAGE->cm->get_course();
        $courseformat = course_get_format($course);
        $renderer = $courseformat->get_renderer($PAGE);

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Get a list of all sections in the course.
        // If a section has restrictions, it should not be included in the list.
        // For each section get all child sections and add them to the list.
        $sectionsvisible = [];
        $sectionclass = $courseformat->get_output_classname('state\\section');
        $sections = $courseformat->get_sections();
        foreach ($sections as $section) {
            if ($courseformat->is_section_visible($section)) {
                // Only return this section data if it's visible by current user on the course page.
                $sectionstate = new $sectionclass($courseformat, $section);
                $exportstate = $sectionstate->export_for_template($renderer);
                if (!$exportstate->hasrestrictions) {
                    $sectionsvisible[] = $exportstate;
                }
            }
        }
        $sectionids = array_map('intval', array_column($sectionsvisible, 'id'));
        $sectionidsvisible = array_merge($sectionids, $this->get_children($sectionsvisible));

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            if (!in_array(intval($module->section), $sectionidsvisible)) {
                continue;
            }
            $mods[$module->id] = $module;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($PAGE->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }
        // Check if there is a previous module to display.
        if ($prevmod) {
            $linkurl = new moodle_url($prevmod->url, ['forceview' => 1]);
            $linkname = $prevmod->get_formatted_name();
            if (!$prevmod->visible) {
                $linkname .= ' ' . get_string('hiddenwithbrackets');
            }

            $attributes = [
                'class' => 'btn btn-link',
                'id' => 'prev-activity-link',
            ];
            $this->prevlink = new \action_link($linkurl, $linkname, null, $attributes);
        }

        // Check if there is a next module to display.
        if ($nextmod) {
            $linkurl = new \moodle_url($nextmod->url, ['forceview' => 1]);
            $linkname = $nextmod->get_formatted_name();
            if (!$nextmod->visible) {
                $linkname .= ' ' . get_string('hiddenwithbrackets');
            }

            $attributes = [
                'class' => 'btn btn-link',
                'id' => 'next-activity-link',
            ];
            $this->nextlink = new \action_link($linkurl, $linkname, null, $attributes);
        }
    }

    /**
     * Recursive function to Get the children of a section.
     * @param array $sectiontree
     */
    public function get_children($sectiontree) {
        $children = [];
        foreach ($sectiontree as $section) {
            // Turn $section into an object if it is an array.
            if (is_array($section)) {
                $section = (object)$section;
            }
            $children[] = intval($section->id);
        }
        return $children;
    }


    /**
     * Export the content to the renderer.
     *
     * @param \renderer_base $output
     */
    public function export_for_template(\renderer_base $output) {
        $prevlink = null;
        if ($this->prevlink) {
            $prevlink = $this->prevlink->export_for_template($output);
        }
        $nextlink = null;
        if ($this->nextlink) {
            $nextlink = $this->nextlink->export_for_template($output);
        }
        return [
            'prevlink' => $prevlink,
            'nextlink' => $nextlink,
        ];
    }
}
