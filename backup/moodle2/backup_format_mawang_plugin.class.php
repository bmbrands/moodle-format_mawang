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
 * Specialised restore for format_mawang (based on the equivalent for format_topics
 *
 * @package   format_mawang
 * @category  backup
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup for format_mawang
 *
 * Ensure that photo background images are included in course backups.
 *
 * @package   format_mawang
 * @category  backup
 * @copyright 2019 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_format_mawang_plugin extends backup_format_plugin {

    /**
     * Carries out some checks at start of course backup.
     *
     * @throws moodle_exception
     */
    public function define_course_plugin_structure() {
        $this->pre_backup_steps();
    }

    /**
     * Returns the format information to attach to section element.
     * @return backup_plugin_element
     * @throws base_element_struct_exception
     */
    protected function define_section_plugin_structure() {
        $parent = $this->get_plugin_element(null, $this->get_format_condition(), 'mawang');

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Create a nested element under each backed up section, this is just a dummy container.
        $imageswrapper = new backup_nested_element(
            'sectionimage',
            [ 'id' ],
            [ 'contenthash', 'pathnamehash', 'filename', 'mimetype' ]
        );
        $imageswrapper->set_source_table(
            'files',
            [
                'itemid' => backup::VAR_SECTIONID,
                'component' => backup_helper::is_sqlparam('format_mawang'),
                'filearea' => backup_helper::is_sqlparam('sectionimage'),
            ]);

        // Annotate files in the format_cards/image filearea for this course's context ID
        // The itemid doesn't get mapped to the new section id, if it changes.
        $imageswrapper->annotate_files(
            'format_mawang',
            'sectionimage',
            null
        );

        $pluginwrapper->add_child($imageswrapper);

        $parent->add_child($pluginwrapper);

        return $parent;
    }

    /**
     * Carry out some initial steps before we start backup.
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function pre_backup_steps() {
        global $DB;
        $courseid = $this->step->get_task()->get_courseid();
        $format = $DB->get_field('course', 'format', ['id' => $courseid]);
        if ($format !== 'mawang') {
            return;
        }
    }
}
