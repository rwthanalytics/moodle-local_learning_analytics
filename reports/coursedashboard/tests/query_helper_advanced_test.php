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
 * Data provider tests.
 *
 * @package    tool_log
 * @category   test
 * @copyright  2018 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use lareport_coursedashboard\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
require_once(__DIR__ . '/../../../../../config.php');

//use: vendor\bin\phpunit local_Learning_Analytics_reports_coursedashboard_testcase local\Learning_Analytics\reports\coursedashboard\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_coursedashboard_testcase extends \advanced_testcase {

    public function test_weekly_activity() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        /*$this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics_log', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics_log');
        get_log_manager(true);*/

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $date = new DateTime();
        $date->modify('-1 week');

        $entry = [
            'id' => 2,
            'eventid' => 30,
            'timecreated' => $date->getTimestamp(),
            'courseid' => $course->id,
            'contextid' => 46,
            'device' => 3611
        ];
        $DB->insert_record_raw('logstore_lanalytics_log', $entry, false, false, true);

        /*$event = report_viewed::create(array(
            'contextid' => $PAGE->context->id,
            'objectid' => report_list::list['coursedashboard'],
        ));
        $event->add_record_snapshot('course', $course);
        //$event->trigger();
        $this->getDataGenerator()->create_event($event);*/

        /*$query2 = <<<SQL
        SELECT *
        FROM {logstore_lanalytics_log} l        
SQL;

        var_dump($DB->get_record_sql($query2));*/
        
        //$testcourse = (int) $DB->get_record_select('course', 'id'>1, null,'id')->id;
        $testweekresult = query_helper::query_weekly_activity($course->id);
        $testweek = end($testweekresult);
        //var_dump($testweekresult);
        $testweekclicks = $testweek->clicks;

        $query = <<<SQL
        SELECT COUNT(*) clicks
        FROM {logstore_lanalytics_log} l
SQL;

        $manualresult = $DB->get_record_sql($query);

        $this->assertEquals($testweekclicks, $manualresult->clicks);
    }
}
