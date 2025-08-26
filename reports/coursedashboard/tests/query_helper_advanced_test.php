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

use lareport_coursedashboard\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
require_once(__DIR__ . '/../../../../../config.php');

//use: navigate in cmd to your moodle folder and enter vendor\bin\phpunit local_Learning_Analytics_reports_coursedashboard_testcase local\Learning_Analytics\reports\coursedashboard\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_coursedashboard_testcase extends \advanced_testcase {

    public function test_weekly_activity() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();

        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics_log', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics_log');

        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $user = $datagenerator->create_user();
        $datagenerator->enrol_user($user->id, $course->id);

        $date = time() - (7 * 24 * 60 * 60);
        $tooearlydate = time() - (7 * 24 * 60 * 60 * 2);

        $entry1 = [
            'id' => 1,
            'eventid' => 30,
            'timecreated' => $tooearlydate,
            'courseid' => $course->id,
            'contextid' => 46,
            'device' => 3611
        ];
        $entry2 = [
            'id' => 2,
            'eventid' => 30,
            'timecreated' => $date,
            'courseid' => $course->id,
            'contextid' => 46,
            'device' => 3611
        ];

        $DB->insert_record('logstore_lanalytics_log', $entry1, false, false, true);
        $DB->insert_record('logstore_lanalytics_log', $entry2, false, false, true);

        $testweekresult = query_helper::query_weekly_activity($course->id);
        $testweek = end($testweekresult);
        $testweekclicks = $testweek->clicks;

        $this->assertEquals(1, $testweekclicks);
    }
}
