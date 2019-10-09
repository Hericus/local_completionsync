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
 * General functions for completionsync plugin.
 *
 * @package     local_completionsync
 * @copyright   2019 Michael Gardener <mgardener@cissq.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the completionsync item
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass        $course     The course to object for the tool
 * @param context         $context    The context of the course
 */
function local_completionsync_extend_navigation_course($navigation, $course, $context) {
    $completion = new completion_info($course);
    if (!$completion->is_enabled()) {
        return;
    }
    if (has_capability('moodle/course:update', $context)) {
        $url = new moodle_url('/local/completionsync/completionsync.php', array('id' => $course->id));
        $name = get_string('pluginname', 'local_completionsync');
        $navigation->add($name, $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/settings', ''));
    }
}