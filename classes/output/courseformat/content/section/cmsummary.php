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
use completion_info;

/**
 * Class cmsummary
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cmsummary extends \core_courseformat\output\local\content\section\cmsummary {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {

        list($mods, $complete, $total, $showcompletion, $purposecount, $totalduration) = $this->calculate_section_stats();

        $totalactivities = array_reduce($mods, fn($carry, $item) => $carry + ($item["count"] ?? 0), 0);
        $data = (object)[
            'showcompletion' => $showcompletion,
            'total' => $total,
            'complete' => $complete,
            'mods' => array_values($mods),
            'totalactivities' => $totalactivities,
            'purposecount' => $purposecount,
            'totalduration' => $totalduration,
        ];

        $data->modprogress = get_string('progresstotal', 'completion', $data);

        return $data;
    }

    /**
     * Calculate the activities count of the current section.
     *
     * @return array with [[count by activity type], completed activities, total of activitites]
     */
    private function calculate_section_stats(): array {
        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;
        $modinfo = $format->get_modinfo();
        $completioninfo = new completion_info($course);

        $mods = [];
        $total = 0;
        $complete = 0;
        $activitycount = 0;
        $readingcount = 0;
        $totalduration = 0;
        $videocount = 0;
        $purposecounts = [];
        $metadata = [];

        $cmids = $modinfo->sections[$section->section] ?? [];

        $cancomplete = isloggedin() && !isguestuser();
        $showcompletion = false;
        foreach ($cmids as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->uservisible) {
                if (isset($mods[$thismod->modname])) {
                    $mods[$thismod->modname]['name'] = $thismod->modplural;
                    $mods[$thismod->modname]['count']++;
                } else {
                    $mods[$thismod->modname]['name'] = $thismod->modfullname;
                    $mods[$thismod->modname]['count'] = 1;
                }
                $purpose = plugin_supports('mod', $thismod->modname, FEATURE_MOD_PURPOSE, MOD_PURPOSE_OTHER);
                if ($thismod->modname == 'video') {
                    $videocount++;
                } else if ($purpose == MOD_PURPOSE_CONTENT) {
                    $readingcount++;
                } else {
                    $activitycount++;

                }
                $totalduration += $this->format->get_cm_duration($thismod->id);
                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $showcompletion = true;
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);
                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }

        if ($activitycount > 0) {
            $purposecounts[] = (object)['name' => get_string('activity', 'format_mawang', $activitycount), 'icon' => 'activity'];
        }
        if ($readingcount > 0) {
            $purposecounts[] = (object)['name' => get_string('reading', 'format_mawang', $readingcount), 'icon' => 'reading'];
        }
        if ($videocount > 0) {
            $purposecounts[] = (object)['name' => get_string('video', 'format_mawang', $videocount), 'icon' => 'video'];
        }
        $totalduration = $this->format->durationstring($totalduration);
        return [$mods, $complete, $total, $showcompletion, $purposecounts, $totalduration];
    }
}
