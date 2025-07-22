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
 * Strings for component mawang course format.
 *
 * @package   format_mawang
 * @copyright 2025 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['backtocourse'] = 'Back to course \'{$a}\'';
$string['backtosection'] = 'Back to \'{$a}\'';
$string['cmbacklink'] = 'Display back link in activities';
$string['cmbacklinkdesc'] = 'Display link \'Back to ...\' allowing to return to the course section inside the section activities.';
$string['confirmdelete'] = 'Are you sure you want to delete this section? All activities and subsections will also be deleted';
$string['confirmmerge'] = 'Are you sure you want to merge this section content with the parent? All activities and subsections will be moved';
$string['courseindexfull'] = 'Sections and activities';
$string['courseindexnone'] = 'Do not display';
$string['currentsection'] = 'This section';
$string['deletesection'] = 'Delete section';
$string['displaycontent'] = 'Display content';
$string['editsection'] = 'Edit section';
$string['editsectionname'] = 'Edit section name';
$string['hidefromothers'] = 'Hide section';
$string['movebeforecm'] = 'Before activity \'{$a}\'';
$string['movebeforesection'] = 'Before \'{$a}\'';
$string['movecmendofsection'] = 'To the end of section \'{$a}\'';
$string['movecmsection'] = 'To the section \'{$a}\'';
$string['movesectiontotheend'] = 'To the end';
$string['newsectionname'] = 'New name for section {$a}';
$string['pluginname'] = 'mawang format';
$string['plugin_description'] = 'A Cambridge University Press course format that displays the course content in a card format, allowing for a more visual and engaging learning experience.';
$string['privacy:metadata'] = 'The mawang format plugin does not store any personal data.';
$string['section0name'] = 'General';
$string['sectionname'] = 'Topic';
$string['showascard'] = 'Display as a card';
$string['showexpanded'] = 'Display after cards';
$string['showfromothers'] = 'Show section';
$string['showsection0title'] = 'Show top section title';
$string['showsection0title_help'] = 'Show the title of the top section on the course page.';

// New strings for the Grid tiles.
$string['sectionimage'] = 'Section image';

// Completion widget.
$string['close'] = 'Close';
$string['notavailable'] = 'Not available';
$string['defaultsectionimage'] = 'Default section image';
$string['defaultsectionimagedesc'] = 'This image will be used as a default section image if the section does not have its own image.';

$string['reading'] = '{$a} reading';
$string['activity'] = '{$a} activity';
$string['progress'] = '{$a}% Progress';
$string['lessons'] = 'Lessons';
$string['video'] = '{$a} video';
$string['recentlyviewed'] = 'Recently viewed';
$string['strftimedayonly'] = '%A';
$string['coursestartdate'] = 'This course starts on {$a}';
$string['coursestartstoday'] = 'This course starts today';

$string['durationlabel'] = 'Duration';
$string['durationcustomfieldname'] = 'Duration custom field name';
$string['durationcustomfieldnamedesc'] = 'The name of the custom field that contains the duration of the activity. This field is used to display the duration in the course format.';
$string['isvideocustomfieldname'] = 'Is video custom field name';
$string['isvideolabel'] = 'Is video';
$string['isvideocustomfieldnamedesc'] = 'The name of the custom field that indicates whether the activity is a video. This field is used for the video count on the course section card.';
$string['cachedef_videos'] = 'Cache for videos in the mawang format';