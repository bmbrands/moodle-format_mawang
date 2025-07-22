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
 * This file contains main class for mawang course format.
 *
 * @package   format_mawang
 * @copyright 2025 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/course/format/lib.php');

use format_mawang\constants;
use core\output\inplace_editable;

define('FORMAT_MAWANG_COLLAPSED', 1);
define('FORMAT_MAWANG_EXPANDED', 0);

/**
 * Main class for the mawang course format.
 *
 * @package    format_mawang
 * @copyright  2025 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_mawang extends core_courseformat\base {

    /**
     * Constructor
     *
     * Overrides the constructor to set the drawer-open-index user preference.
     *
     * @param string $format
     * @param int $courseid
     */
    public function __construct($format, $courseid) {
        global $PAGE;

        // Need to add this bodyclass to hide blocks when custom fields tab is active.
        $tab = optional_param('tab', '', PARAM_INT);
        if ($tab) {
            $PAGE->add_body_class('has-customfields-tab');
        }

        if (strpos($PAGE->pagetype, 'mod-') === 0) {
            $PAGE->set_secondary_navigation(false);
        }
        $openblocksfor = get_config('format_mawang', 'autoblockopen');
        if ($openblocksfor) {
            $openblocksfor = explode(',', $openblocksfor);
            $pagetypes = [];
            foreach ($openblocksfor as $modulename) {
                $pagetypes[] = 'mod-' . $modulename . '-view';
            }
            if (in_array($PAGE->pagetype, $pagetypes)) {
                set_user_preference('drawer-open-block', true);
            }
        }

        parent::__construct($format, $courseid);
    }

    /**
     * Returns true if this course format uses sections.
     *
     * @return bool
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Uses course index
     *
     * @return bool
     */
    public function uses_course_index() {
        return true;
    }

    /**
     * Uses indentation
     *
     * @return bool
     */
    public function uses_indentation(): bool {
        return (get_config('format_mawang', 'indentation')) ? true : false;
    }

    /**
     * Maximum number of subsections
     *
     * @return int
     */
    public function get_max_section_depth(): int {
        $limit = (int)get_config('format_mawang', 'maxsectiondepth');
        return max(1, min($limit, 100));
    }

    /**
     * Returns the display name of the given section that the course prefers.
     *
     * Use section name is specified by user. Otherwise use default ("Topic #").
     *
     * @param int|stdClass|section_info $section Section object from database or just field section.section
     * @return string Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        if (!is_object($section)) {
            $section = $this->get_section($section);
        }
        if ((string)$section->name !== '') {
            return format_string($section->name, true,
                ['context' => context_course::instance($this->courseid)]);
        } else {
            return $this->get_default_section_name($section);
        }
    }

    /**
     * Returns the default section name for the mawang course format.
     *
     * If the section number is 0, it will use the string with key = section0name from the course format's lang file.
     * If the section number is not 0, the base implementation of course_format::get_default_section_name which uses
     * the string with the key = 'sectionname' from the course format's lang file + the section number will be used.
     *
     * @param stdClass|section_info $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        if ($section->section == 0) {
            // Return the general section.
            return get_string('section0name', 'format_mawang');
        } else {
            // Use course_format::get_default_section_name implementation which
            // will display the section name in "Topic n" format.
            return parent::get_default_section_name($section);
        }
    }

    /**
     * Generate the title for this section page.
     *
     * @return string the page title
     */
    public function page_title(): string {
        global $CFG;
        if ((int)$CFG->branch >= 404) {
            // TODO it is possible it is not used anymore. Review.
            return 'Topic outline';
        } else {
            return get_string('topicoutline');
        }
    }

    /**
     * Returns the section relative number regardless whether argument is an object or an int
     *
     * @param int|section_info $section
     * @return ?int
     */
    protected function resolve_section_number($section) {
        if ($section === null || $section === '') {
            return null;
        } else if (is_object($section)) {
            return $section->section;
        } else {
            return (int)$section;
        }
    }

    /**
     * Returns the information about the ajax support in the given source format.
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Supports components
     *
     * @return bool
     */
    public function supports_components() {
        return true;
    }

    /**
     * Loads all of the course sections into the navigation.
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     * @return void
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        // If course format displays section on separate pages and we are on course/view.php page
        // and the section parameter is specified, make sure this section is expanded in
        // navigation.
        if ($navigation->includesectionnum === false && $this->get_viewed_section() &&
            (!defined('AJAX_SCRIPT') || AJAX_SCRIPT == '0')) {
            $navigation->includesectionnum = $this->get_viewed_section();
        }

        $modinfo = get_fast_modinfo($this->courseid);
        if (!empty($modinfo->sections[0])) {
            foreach ($modinfo->sections[0] as $cmid) {
                $this->navigation_add_activity($node, $modinfo->get_cm($cmid));
            }
        }
        foreach ($modinfo->get_section_info_all() as $section) {
            $this->navigation_add_section($navigation, $node, $section);
        }
        // $currentsection = $this->get_viewed_section();
        // $this->navigation_add_section($navigation, $node, $modinfo->sections[0]);

    }

    /**
     * Adds a course module to the navigation node
     *
     * @param navigation_node $node
     * @param cm_info $cm
     * @return null|navigation_node
     */
    protected function navigation_add_activity(navigation_node $node, cm_info $cm): ?navigation_node {
        if (!$cm->uservisible || !$cm->has_view()) {
            return null;
        }
        $activityname = $cm->get_formatted_name();
        $action = $cm->url;
        if ($cm->icon) {
            $icon = new pix_icon($cm->icon, $cm->modfullname, $cm->iconcomponent);
        } else {
            $icon = new pix_icon('icon', $cm->modfullname, $cm->modname);
        }
        $activitynode = $node->add($activityname, $action, navigation_node::TYPE_ACTIVITY, null, $cm->id, $icon);
        if (global_navigation::module_extends_navigation($cm->modname)) {
            $activitynode->nodetype = navigation_node::NODETYPE_BRANCH;
        } else {
            $activitynode->nodetype = navigation_node::NODETYPE_LEAF;
        }
        if (method_exists($cm, 'is_visible_on_course_page')) {
            $activitynode->display = $cm->is_visible_on_course_page();
        }
        return $activitynode;
    }

    /**
     * Adds a section to navigation node, loads modules and subsections if necessary
     *
     * @param global_navigation $navigation
     * @param navigation_node $node
     * @param section_info $section
     * @return null|navigation_node
     */
    protected function navigation_add_section($navigation, navigation_node $node, section_info $section): ?navigation_node {
        if (!$section->uservisible) {
            return null;
        }
        $sectionname = get_section_name($this->get_course(), $section);
        $url = course_get_url($this->get_course(), $section->section, ['navigation' => true]);

        $sectionnode = $node->add($sectionname, $url, navigation_node::TYPE_SECTION, null, $section->id);
        $sectionnode->nodetype = navigation_node::NODETYPE_BRANCH;
        $sectionnode->hidden = false;
        if ($section->section == $this->get_viewed_section()) {
            $sectionnode->force_open();
        }
        if ($navigation->includesectionnum == $section->section) {
            $modinfo = get_fast_modinfo($this->courseid);
            if (!empty($modinfo->sections[$section->section])) {
                foreach ($modinfo->sections[$section->section] as $cmid) {
                    $this->navigation_add_activity($sectionnode, $modinfo->get_cm($cmid));
                }
            }
        }
        return $sectionnode;
    }

    /**
     * Custom action after section has been moved in AJAX mode.
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = [];
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return ['sectiontitles' => $titles, 'action' => 'move'];
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course.
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return [
            BLOCK_POS_LEFT => [],
            BLOCK_POS_RIGHT => [],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for section
     *
     * See {@see format_base::course_format_options()} for return array definition.
     *
     * Additionally section format options may have property 'cache' set to true
     * if this option needs to be cached in {@see get_fast_modinfo()}. The 'cache' property
     * is recommended to be set only for fields used in {@see format_base::get_section_name()},
     * {@see format_base::extend_course_navigation()} and {@see format_base::get_view_url()}
     *
     * For better performance cached options are recommended to have 'cachedefault' property
     * Unlike 'default', 'cachedefault' should be static and not access get_config().
     *
     * Regardless of value of 'cache' all options are accessed in the code as
     * $sectioninfo->OPTIONNAME
     * where $sectioninfo is instance of section_info, returned by
     * get_fast_modinfo($course)->get_section_info($sectionnum)
     * or get_fast_modinfo($course)->get_section_info_all()
     *
     * All format options for particular section are returned by calling:
     * $this->get_format_options($section);
     *
     * @param bool $foreditform
     * @return array
     */
    public function section_format_options($foreditform = false): array {
        return [
            'visibleold' => [
                'type' => PARAM_INT,
                'label' => '',
                'element_type' => 'hidden',
                'default' => 1,
                'cache' => true,
                'cachedefault' => 0,
            ],
            'collapsed' => [
                'type' => PARAM_INT,
                'label' => get_string('displaycontent', 'format_mawang'),
                'element_type' => 'select',
                'element_attributes' => [
                    [
                        FORMAT_MAWANG_COLLAPSED => new lang_string('showascard', 'format_mawang'),
                        FORMAT_MAWANG_EXPANDED => new lang_string('showexpanded', 'format_mawang'),
                    ],
                ],
                'cache' => true,
                'cachedefault' => FORMAT_MAWANG_COLLAPSED,
                'default' => COURSE_DISPLAY_SINGLEPAGE,
            ],
            'sectionimage' => [
                'type' => PARAM_RAW,
                'label' => get_string('sectionimage', 'format_mawang'),
                'element_type' => 'filepicker',
                'cache' => true,
                'cachedefault' => '',
                'default' => '',
            ],
        ];
    }

    /**
     * Definitions of the additional options that this course format uses for course.
     *
     * Flexsections format uses the following options:
     * - showsection0title
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseformatoptions = [
                'cmbacklink' => [
                    'default' => (bool)get_config('format_mawang', 'cmbacklink'),
                    'type' => PARAM_BOOL,
                ],
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay ?? COURSE_DISPLAY_SINGLEPAGE,
                    'type' => PARAM_INT,
                ),
            ];
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $options = [
                constants::COURSEINDEX_FULL => get_string('courseindexfull', 'format_mawang'),
                constants::COURSEINDEX_NONE => get_string('courseindexnone', 'format_mawang'),
            ];
            $courseformatoptionsedit = [
                'cmbacklink' => [
                    'label' => new lang_string('cmbacklink', 'format_mawang'),
                    'element_type' => 'advcheckbox',
                ],
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                ),
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * This function is called from {@see course_edit_form::definition_after_data()}.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $COURSE;
        $elements = parent::create_edit_form_elements($mform, $forsection);

        if (!$forsection && (empty($COURSE->id) || $COURSE->id == SITEID)) {
            // Add "numsections" element to the create course form - it will force new course to be prepopulated
            // with empty sections.
            // The "Number of sections" option is no longer available when editing course, instead teachers should
            // delete and add sections when needed.
            $courseconfig = get_config('moodlecourse');
            $max = (int)$courseconfig->maxsections;
            $element = $mform->addElement('select', 'numsections', get_string('numberweeks'), range(0, $max ?: 52));
            $mform->setType('numsections', PARAM_INT);
            if (is_null($mform->getElementValue('numsections'))) {
                $mform->setDefault('numsections', $courseconfig->numsections);
            }
            array_unshift($elements, $element);
        }

        return $elements;
    }

    /**
     * Whether this format allows to delete sections.
     *
     * Do not call this function directly, instead use {@see course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return inplace_editable|void
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        return parent::inplace_editable_render_section_name($section, $linkifneeded, $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return !$section->section || $section->visible;
    }

    /**
     * Callback used in WS core_course_edit_section when teacher performs an AJAX action on a section (show/hide).
     *
     * Access to the course is already validated in the WS but the callback has to make sure
     * that particular action is allowed by checking capabilities
     *
     * Course formats should register.
     *
     * @param section_info|stdClass $section
     * @param string $action
     * @param int $sr
     * @return null|array any data for the Javascript post-processor (must be json-encodeable)
     */
    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'mawang' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        if ($section->section && ($action === 'showexpanded' || $action === 'showcollapsed')) {
            require_capability('moodle/course:update', context_course::instance($this->courseid));
            $newvalue = ($action === 'showexpanded') ? FORMAT_MAWANG_EXPANDED : FORMAT_MAWANG_COLLAPSED;
            course_update_section($this->courseid, $section, ['collapsed' => $newvalue]);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_mawang');

        if (!($section instanceof section_info)) {
            $modinfo = course_modinfo::instance($this->courseid);
            $section = $modinfo->get_section_info($section->section);
        }
        $elementclass = $this->get_output_classname('content\\section\\availability');
        $availability = new $elementclass($this, $section);

        $rv['section_availability'] = $renderer->render($availability);
        return $rv;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of configuration settings
     */
    public function get_config_for_external() {
        // Return everything (nothing to hide).
        return $this->get_format_options();
    }

    /**
     * Checks if section is really available for the current user (analyses parent section available)
     *
     * @deprecated since Moodle 4.5
     * @param int|section_info $section
     * @return bool
     */
    public function is_section_real_available($section) {
        if (($this->resolve_section_number($section) == 0)) {
            // Section 0 is always available.
            return true;
        }
        $context = context_course::instance($this->courseid);
        if (has_capability('moodle/course:viewhiddensections', $context)) {
            // For the purpose of this function only return true for teachers.
            return true;
        }
        $section = $this->get_section($section);
        return $section->available;
    }

    /**
     * URL of the page from where this function was called (use referer if this is an AJAX request)
     *
     * @return moodle_url
     */
    protected function get_caller_page_url(): moodle_url {
        global $PAGE, $FULLME;
        $url = $PAGE->has_set_url() ? $PAGE->url : new moodle_url($FULLME);
        if ($url->compare(new moodle_url('/lib/ajax/service.php'), URL_MATCH_BASE)) {
            return !empty($_SERVER['HTTP_REFERER']) ? new moodle_url($_SERVER['HTTP_REFERER']) : $url;
        }
        return $url;
    }

    /**
     * Returns true if we are on /course/view.php page
     *
     * @return bool
     */
    public function on_course_view_page() {
        $url = $this->get_caller_page_url();
        return ($url && $url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE));
    }

    /**
     * If we are on course/view.php page return the 'section' attribute from query
     *
     * @return int
     */
    public function get_viewed_section() {
        if ($this->on_course_view_page()) {
            if ($s = $this->get_caller_page_url()->get_param('section')) {
                return (int)$s;
            }
            $sid = $this->get_caller_page_url()->get_param('sectionid');
            if ($sid && ($section = $this->get_modinfo()->get_section_info_by_id($sid))) {
                return $section->section;
            }
        }
        return 0;
    }

    /**
     * Sets the section visible/hidden including subsections and modules
     *
     * @param int|stdClass|section_info $section
     * @param int $visibility
     * @param null|int $setvisibleold if specified in case of hiding the section,
     *    this will be the value of visibleold for the section $section.
     */
    protected function set_section_visible($section, $visibility, $setvisibleold = null) {
        $subsections = [];
        $sectionnumber = $this->resolve_section_number($section);
        if (!$sectionnumber && !$visibility) {
            // Can not hide section with number 0.
            return;
        }
        $section = $this->get_section($section);

        if ($visibility) {
            if ($section->visibleold) {
                set_section_visible($this->courseid, $section->section, $section->visibleold);
            }
        } else {
            if ($section->visible) {
                set_section_visible($this->courseid, $section->section, $visibility);
                $this->update_section_format_options(['id' => $section->id, 'visibleold' => $section->visible]);
            }
        }
    }

    /**
     * Method used to get the maximum number of sections for this course format.
     *
     * Flexsections does not have a limit for the total number of the sections.
     *
     * @return int
     */
    public function get_max_sections() {
        return 9999999;
    }

    /**
     * Method used to get the maximum number of sections on the top level.
     * @return int
     */
    public function get_max_toplevel_sections() {
        $maxsections = get_config('moodlecourse', 'maxsections');
        if (!isset($maxsections) || !is_numeric($maxsections)) {
            $maxsections = 52;
        }
        return $maxsections;
    }

    /**
     * Get the section image URL
     *
     * @param int $sectionid
     * @return string
     */
    public function get_section_image(int $sectionid): string {
        $context = context_course::instance($this->courseid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'format_mawang', 'sectionimage', $sectionid);
        if ($files) {
            foreach ($files as $file) {
                $name = $file->get_filename();
                if ($name !== '.') {
                    return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                        $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
                }
            }
        }
        // If no image is found, return the default image.
        return $this->get_default_section_image();
    }

    /**
     * Updates format options for a section
     *
     * Section id is expected in $data->id (or $data['id'])
     * If $data does not contain property with the option name, the option will not be updated
     *
     * @param stdClass|array $data return value from moodleform::get_data() or array with data
     * @return bool whether there were any changes to the options values
     */
    public function update_section_format_options($data) {
        parent::update_section_format_options($data);
        $context = context_course::instance($this->courseid);
        $data = $data;
        // Get the sectionimage Image file from the form data and save it to the sectionimage file area.
        if (isset($data['sectionimage'])) {
            file_save_draft_area_files(
                $data['sectionimage'],
                $context->id,
                'format_mawang',
                'sectionimage',
                $data['id'],
                [
                    'subdirs' => 0,
                    'maxfiles' => 50,
                ]
            );
        }
        return $this->update_format_options($data, $data['id']);
    }

    /**
     * Returns a renderable footer
     */
    public function course_content_footer() {
        return new \format_mawang\output\course_content_footer('test');
    }

    /**
     * Returns the default section image URL
     * @return string
     */
    public function get_default_section_image(): string {
        global $OUTPUT;
        // Get the configured default section image.
        $defaultsectionimage = get_config('format_mawang', 'defaultsectionimage');
        if ($defaultsectionimage) {
            $filepath = '/';
            return moodle_url::make_pluginfile_url(
                context_system::instance()->id,
                'format_mawang',
                'defaultsectionimage',
                $filepath,
                theme_get_revision(),
                $defaultsectionimage
            );
        }
        return $OUTPUT->image_url('defaultsectionimage', 'format_mawang');
    }

    /**
     * Get the content of a course tab.
     * the tab is related to a category of course custom fields.
     * @param int $tab
     * @return array Of objects grouped by category, examlpe
     * [
     *   ['category' => ['name' => 'Category 1', 'shortname' => 'cat1', 'fields' => [
     *      ['name' => 'Field 1', 'shortname' => 'field1', 'value' => 'Value 1'],
     *     ['name' => 'Field 2', 'shortname' => 'field2', 'value' => 'Value 2'],
     *   ]],
     *   ['category' => ['name' => 'Category 2', 'shortname' => 'cat2', 'fields' => [...
     *
     */
    public function get_customfields_tab_content(int $tab): array {
        $handler = \core_course\customfield\course_handler::create();
        $datas = $handler->get_instance_data($this->courseid);
        $metadata = [];
        foreach ($datas as $data) {
            if (empty($data->get_value())) {
                continue;
            }
            $cat = $data->get_field()->get_category()->get('name');
            $catid = $data->get_field()->get_category()->get('id');
            if ($catid != $tab) {
                continue;
            }
            $shortnames = $data->get_field()->get('shortname');
            $fullname = $data->get_field()->get('name');
            $value = $data->export_value();
            if (!isset($metadata[$cat])) {
                $metadata[$cat] = [
                    'name' => $cat,
                    'fields' => [],
                ];
            }
            $metadata[$cat]['fields'][] = [
                'name' => $fullname,
                'shortname' => $shortnames,
                'value' => $value,
            ];
        }
        return array_values($metadata);
    }

    /**
     * Get the duration of a course module in minutes.
     * @param int $cmid
     * @return int
     */
    public function get_cm_duration(int $cmid): int {
        $handler = \local_modcustomfields\customfield\mod_handler::create();
        $fieldname = get_config('format_mawang', 'durationcustomfieldname');
        $datas = $handler->get_instance_data($cmid);
        $duration = 0;
        foreach ($datas as $data) {
            $field = $data->get_field();
            $shortname = $field->get('shortname');
            $value = $data->get_value();
            if ($shortname == trim($fieldname)) {
                $duration = Intval($value);
            }
        }
        return $duration;
    }

    /**
     * Check to see if a course module is or contains a video.
     * @param int $cmid
     * @return bool
     */
    public function is_cm_video(int $cmid): bool {
        global $DB;

        $fieldname = get_config('format_mawang', 'isvideocustomfieldname');
        if (empty($fieldname)) {
            return false; // No custom field configured.
        }
        $videocache = cache::make('format_mawang', 'videos');
        if ($videocache->has($fieldname)) {
            $records = $videocache->get($fieldname);
            if (in_array($cmid, $records)) {
                return true;
            } else {
                return false; 
            }
        }

        $field = $DB->get_record('customfield_field', ['shortname' => trim($fieldname)], '*', MUST_EXIST);
        if (!$field) {
            return false; // No custom field found.
        }
        $videocache = cache::make('format_mawang', 'videos');
        $sql = "SELECT instanceid
                FROM {customfield_data}
                WHERE fieldid = :fieldid
                AND " . $DB->sql_compare_text('value') . " = :value";
        $params = ['fieldid' => $field->id, 'value' => '1'];
        $records = $DB->get_records_sql($sql, $params);
        $instanceids = [];
        foreach ($records as $value) {
            $instanceids[] = $value->instanceid;
            if ($value->instanceid == $cmid) {
                return true;
            }
        }
        $videocache->set($fieldname, $instanceids);
        return false;
    }

    /**
     * Get the displayable duration of something.
     *
     * Reformat the number of seconds to a human readable format.
     * So an input of 90 will return 1h 30m.
     *
     * @param int $minutes
     * @return string
     */
    public function durationstring(int $minutes): string {
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;
        $displayable = '';
        if ($hours > 0) {
            $displayable .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $displayable .= $minutes . 'm';
        }
        return trim($displayable);
    }

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return ?inplace_editable
 */
function format_mawang_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/externallib.php');
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            [$itemid, 'mawang'], MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}

/**
 * Get icon mapping for font-awesome.
 */
function format_mawang_get_fontawesome_icon_map() {
    return [
        'format_mawang:mergeup' => 'fa-level-up',
    ];
}

/**
 * If we are on an activity page inside the course in the 'mawang' format - return the activity
 *
 * @return cm_info|null
 */
function format_mawang_add_back_link_to_cm(): ?cm_info {
    global $PAGE, $CFG;
    if ($PAGE->course
            && $PAGE->cm
            && $PAGE->course->format === 'mawang' // Only modules in 'mawang' courses.
            && course_get_format($PAGE->course)->get_course()->cmbacklink
            && $PAGE->pagelayout === 'incourse' // Only view pages with the incourse layout (not popup, embedded, etc).
            && $PAGE->cm->sectionnum // Do not display in activities in General section.
            && $PAGE->url->out_omit_querystring() === $CFG->wwwroot . "/mod/{$PAGE->cm->modname}/view.php") {
        return $PAGE->cm;
    }
    return null;
}

/**
 * Callback allowing to add contetnt inside the region-main, in the very end
 *
 * If we are on activity page, add the "Back to section" link
 *
 * @return string
 */
function format_mawang_before_footer() {
    // This is an implementation of a legacy callback that will only be called in older Moodle versions.
    // It will not be called in Moodle versions that contain the hook core\hook\output\before_footer_html_generation,
    // instead, the callback format_mawang\local\hooks\output\before_footer_html_generation::callback will be executed.

    global $OUTPUT;
    if ($cm = format_mawang_add_back_link_to_cm()) {
        $url = new \moodle_url('/course/section.php', ['id' => $cm->sectionnum]);
        return $OUTPUT->render_from_template('format_mawang/back_link_in_cms', [
            'backtosection' => [
                'url' => $url->out(false),
                'sectionname' => get_section_name($cm->course, $cm->sectionnum),
            ],
        ]);
    }
    return '';
}

/**
 * Serves any files associated with the plugin (e.g. tile photos).
 * For explanation see https://docs.moodle.org/dev/File_API
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return void
 */
function format_mawang_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    $allowecontexts = [CONTEXT_COURSE, CONTEXT_MODULE, CONTEXT_SYSTEM];
    if (!in_array($context->contextlevel, $allowecontexts)) {
        send_file_not_found();
    }
    $fs = get_file_storage();
    if ($filearea == 'defaultsectionimage') {
        $defaultsectionimage = $fs->get_area_files(
            context_system::instance()->id,
            'format_mawang',
            'defaultsectionimage',
            0,
            "filename",
            false
        );
        foreach ($defaultsectionimage as $file) {
            send_stored_file($file, 86400, 0, $forcedownload, $options);
        }
    }
    if ($filearea !== 'sectionimage') {
        debugging('Invalid file area ' . $filearea, DEBUG_DEVELOPER);
        send_file_not_found();
    }

    // Make sure the user is logged in and has access to the course.
    require_login($course);

    $sectionid = (int)$args[0];
    $filepath = '';
    $filename = $args[1];
    $file = $fs->get_file($context->id, 'format_mawang', $filearea, $sectionid, $filepath, $filename);
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
