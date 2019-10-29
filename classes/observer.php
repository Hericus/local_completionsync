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
 * Observer
 *
 * @package     local_completionsync
 * @copyright   2019 Michael Gardener <mgardener@cissq.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_completionsync;

defined('MOODLE_INTERNAL') || die();

/**
 * Class observer
 *
 * @package     local_completionsync
 * @copyright   2019 Michael Gardener <mgardener@cissq.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    public static function course_completed(\core\event\course_completed $event) {
        global $DB, $CFG;

        // Global Setting.
        if (!$enabled = get_config('local_completionsync', 'enabled')) {
            return true;
        }

        $eventdata = $event->get_record_snapshot('course_completions', $event->objectid);
        $userid = $event->relateduserid;
        $courseid = $event->courseid;

        // Course setting.
        if ($disabled = $DB->get_field('local_completionsync', 'disabled', ['course' => $courseid])) {
            return true;
        }

        $sql = "SELECT MAX(mc.timemodified) timecompletion
                  FROM {course_modules_completion} mc
                  JOIN {course_modules} cm
                    ON mc.coursemoduleid = cm.id
                 WHERE cm.course = ?
                   AND mc.userid = ?
                   AND mc.completionstate > ?";

        if ($timecompleted = $DB->get_field_sql($sql, [$courseid, $userid, COMPLETION_INCOMPLETE])) {
            $data = new \stdClass();
            $data->id = $eventdata->id;
            $data->timecompleted = $timecompleted;

            if ($DB->update_record('course_completions', $data)) {
                // Clear coursecompletion cache which was added in Moodle 3.2.
                if ($CFG->version >= 2016120500) {
                    \cache::make('core', 'coursecompletion')->purge();
                }
            }
        }

        return true;
    }
}
