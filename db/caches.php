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
 * Cache definitions for mawang format
 *
 * Documentation: {@link https://docs.moodle.org/dev/Cache_API}
 *
 * @package    format_mawang
 * @category   cache
 * @copyright  2025 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'videos' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simpledata' => true,
        'lifetime' => 86400,
        'invalidationevants' => [
            'course_updated',
            'course_module_updated',
            'course_module_created',
            'course_module_deleted',
            'course_section_updated',
            'course_section_created',
            'course_section_deleted',
        ],
    ],
];
