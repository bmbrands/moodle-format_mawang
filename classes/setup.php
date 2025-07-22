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

namespace format_mawang;

/**
 * Class setup
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setup {
    /**
     * Install method to set up necessary configurations or initializations.
     *
     * @return void
     */
    public static function install(): void {
        $handler = \local_modcustomfields\customfield\mod_handler::create();
        $fields = $handler->get_fields();
        $durationfieldname = constants::DEFAULT_DURATION_CUSTOM_FIELD_NAME;
        $hasdurationfield = false;
        $isvideofield = constants::DEFAULT_ISVIDEO_CUSTOM_FIELD_NAME;
        $hasvideofield = false;
        foreach ($fields as $field) {
            if ($field->get('shortname') === $durationfieldname) {
                $hasdurationfield = true;
            }
            if ($field->get('shortname') === $isvideofield) {
                $hasvideofield = true;
            }
        }
        $categories = \core_customfield\category::get_records(['component' => 'local_modcustomfields']);
        if (empty($categories)) {
            return;
        }
        $category = reset($categories);
        $categoryid = $category->get('id');
        $configdata = [
            'required' => 0,
            'uniquevalues' => 0,
            'defaultvalue' => '',
            'minimumvalue' => '',
            'maximumvalue' => '',
            'decimalplaces' => 0,
            'display' => '{value}',
            'displaywhenzero' => 0,
            'locked' => 0,
            'visibility' => 2,
            'checkbydefault' => 0,
        ];

        if (!$hasdurationfield) {
            $field = new \core_customfield\field();
            $field->set('shortname', $durationfieldname);
            $field->set('name', get_string('durationlabel', 'format_mawang'));
            $field->set('type', 'number');
            $field->set('categoryid', $categoryid);
            $field->set('configdata', json_encode($configdata));
            $field->save();
        }
        if (!$hasvideofield) {
            $field = new \core_customfield\field();
            $field->set('shortname', $isvideofield);
            $field->set('name', get_string('isvideolabel', 'format_mawang'));
            $field->set('type', 'checkbox');
            $field->set('categoryid', $categoryid);
            $field->set('configdata', json_encode($configdata));
            $field->save();
        }
    }

}
