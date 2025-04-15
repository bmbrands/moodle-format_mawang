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

namespace format_mawang\form;
use context;
use moodle_url;
use context_course;
use core_form\dynamic_form;

/**
 * Class section_edit
 *
 * @package    format_mawang
 * @copyright  2024 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class section_edit extends dynamic_form {

    /**
     * Define form
     */
    protected function definition() {
        $mform = $this->_form;
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        $sectionid = $this->optional_param('sectionid', null, PARAM_INT);
        $sectionnum = $this->optional_param('sectionnum', null, PARAM_INT);
        $sectionname = $this->optional_param('sectionname', null, PARAM_TEXT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'sectionid', $sectionid);
        $mform->setType('sectionid', PARAM_INT);
        $mform->addElement('hidden', 'sectionnum', $sectionnum);
        $mform->setType('sectionnum', PARAM_INT);
        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);

        // Add the section image field.
        $mform->addElement('filepicker', 'sectionimage', get_string('sectionimage', 'format_mawang'), null, [
            'maxbytes' => 0,
            'accepted_types' => ['image'],
        ]);
    }

    /**
     * Process dynamic submission
     */
    public function process_dynamic_submission() {
        try {
            $data = (array) $this->get_data();
            $data['id'] = $data['sectionid'];
            course_get_format($data['courseid'])->update_section_format_options($data);
            return [
                'result' => true,
                'returnurl' => ($this->get_page_url_for_dynamic_submission())->out(),
            ];
        } catch (\Exception $e) {
            return [
                'result' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get context for dynamic submission
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $courseid = $this->optional_param('courseid', null, PARAM_INT);
        return context_course::instance($courseid);
    }

    /**
     * Check access for dynamic submission
     */
    protected function check_access_for_dynamic_submission(): void {
        $context = $this->get_context_for_dynamic_submission();
        if (!has_capability('moodle/course:update', $context)) {
            throw new \Exception(get_string('accessdenied', 'admin'));
        }
    }

    /**
     * Set data for dynamic submission
     */
    public function set_data_for_dynamic_submission(): void {
        $data = [
            'courseid' => $this->optional_param('courseid', null, PARAM_INT),
            'sectionid' => $this->optional_param('sectionid', null, PARAM_INT),
            'sectionname' => $this->optional_param('sectionname', null, PARAM_TEXT),
        ];
        parent::set_data((object) $data);
    }

    /**
     * Get page URL for dynamic submission
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        global $CFG;
        $returnurl = $CFG->wwwroot . '/course/view.php';
        $params = [
            'id' => $this->optional_param('courseid', null, PARAM_INT),
        ];
        $sectionnum = $this->optional_param('sectionnum', null, PARAM_INT);
        $anchor = $sectionnum ? 'section-' . $sectionnum : null;
        return new moodle_url($returnurl, $params, $anchor);
    }
}
