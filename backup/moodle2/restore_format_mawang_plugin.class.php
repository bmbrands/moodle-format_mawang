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

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . "/../../lib.php");

/**
 * Specialised restore logic for format_mawang. Handles restoring images used for each card
 *
 * @package     format_mawang
 * @copyright   2024 University of Essex
 * @author      John Maydew <jdmayd@essex.ac.uk>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_format_mawang_plugin extends restore_format_plugin {

    /**
     * The structure format_mawang adds to the backup file is completely irrelevant, as the format doesn't add its
     * own tables and just re-uses data from the course_sections, course_section_options, and files tables
     *
     * @return restore_path_element[]
     */
    public function define_section_plugin_structure(): array {

        $this->add_related_files('format_mawang', 'sectionimage', null);

        return [
            // Keep this for backwards compatibility with previous versions.
            new restore_path_element('sectionimage', $this->get_pathfor('/sectionimage')),
        ];
    }

    /**
     * Dummy method
     *
     * @param array $data
     * @return void
     */
    public function process_sectionimage(array $data): void {
        // No-op.
    }


    /**
     * When a section gets restored the card image file records are restored using the old itemid, which
     * refers to the id of the section from the course the backup was created from
     * We need to do some extra steps to make sure restored images get put back in the right place
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    public function after_restore_section(): void {
        global $DB;
        $data = $this->connectionpoint->get_data();

        if (!isset($data['path'])
            || $data['path'] != "/section"
            || !isset($data['tags']['id'])) {
            return;
        }

        $oldsectionid = $data['tags']['id'];
        $oldsectionnum = $data['tags']['number'];

        $newcourseid = $this->step->get_task()->get_courseid();
        $newsectionid = $DB->get_field('course_sections', 'id', [
            'course' => $newcourseid,
            'section' => $oldsectionnum,
        ]);

        if (!$newsectionid) {
            return;
        }

        self::move_section_image($newcourseid, $oldsectionid, $newsectionid);
    }

    /**
     * Given a course ID and the ID of the restored section, move any restored card images to the
     * correct section
     *
     * @param int $newcourseid ID of the new course
     * @param int $oldsectionid ID of the old section that was backed up
     * @param int $newsectionid ID of the new section we're moving the image to
     * @throws coding_exception
     * @throws file_exception
     * @throws stored_file_creation_exception
     */
    private static function move_section_image(int $newcourseid, int $oldsectionid, int $newsectionid): void {
        $filestorage = get_file_storage();
        $context = context_course::instance($newcourseid);

        // Did we copy an image for the new section?
        $restoredimage = $filestorage->get_area_files(
            $context->id,
            'format_mawang',
            'sectionimage',
            $oldsectionid,
            'itemid, filepath, filename',
            false,
            0,
            0,
            1
        );

        // Nothing to do if no images were restored.
        if (empty($restoredimage)) {
            return;
        }

        $restoredimage = reset($restoredimage);

        // Are there any existing images for this new section?
        $existingimage = $filestorage->get_area_files(
            $context->id,
            'format_mawang',
            'sectionimage',
            $newsectionid,
            'itemid, filepath, filename',
            false,
            0,
            0,
            1
        );

        // If there's an existing image, we need to remove it first.
        if (!empty($existingimage)) {
            $existingimage = reset($existingimage);

            // If this is the same ID we're restoring into the same course,
            // don't delete anything.
            if ($restoredimage->get_id() == $existingimage->get_id()) {
                return;
            }

            // If the IDs are different but the content is the same, delete all the restored
            // images and just leave it.
            if ($restoredimage->get_contenthash() == $existingimage->get_contenthash()) {
                $filestorage->delete_area_files($context->id,
                    'format_mawang',
                    'sectionimage',
                    $oldsectionid
                );
                return;
            }

            $existingimage->delete();
        }

        $movedimage = $filestorage->create_file_from_storedfile(
            [ 'itemid' => $newsectionid ],
            $restoredimage
        );

        // If the section IDs are the same, just delete the extra image we restored.
        if ($oldsectionid == $newsectionid) {
            $restoredimage->delete();
        } else {
            // Otherwise, delete all file records for the old section we restored to
            // keep things tidy.
            $filestorage->delete_area_files($context->id,
                'format_mawang',
                'sectionimage',
                $oldsectionid
            );
        }
    }

    /**
     * All the other cool format plugins have this.
     *
     * @return void
     */
    protected function after_execute_structure(): void {
        // No-op.
    }
}
