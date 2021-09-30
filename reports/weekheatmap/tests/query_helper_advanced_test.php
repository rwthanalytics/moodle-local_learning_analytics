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

use lareport_weekheatmap\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
require_once(__DIR__ . '/../../../../../config.php');

//use: navigate in cmd to your moodle folder and enter vendor\bin\phpunit local_Learning_Analytics_reports_weekheatmap_testcase local\Learning_Analytics\reports\weekheatmap\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_weekheatmap_testcase extends \advanced_testcase {

    public function test_weekly_activity() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $datagenerator = $this->getDataGenerator();
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics');
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $user = $datagenerator->create_user();
        $datagenerator->enrol_user($user->id, $course->id);
        $startdate = new \DateTime();
        $startdate->setTimestamp($course->startdate);
        $startdate->modify('Monday this week');
        $mondaytimestamp = $startdate->getTimestamp();
        $counter = 1;
        for($i=0; $i<168; $i++) {
            $entry = [
                'id' => $counter,
                'eventid' => 30,
                'timecreated' => $mondaytimestamp + $i * 60 * 60,
                'courseid' => $course->id,
                'contextid' => 46,
                'device' => 3611
            ];
            $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
            $counter++;
            if($i%2==0) {
                $entry['id'] = $counter;
                $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
                $counter++;
            }
            if($i%3==0) {
                $entry['id'] = $counter;
                $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
                $counter++;
            }
        }
        var_dump($DB->get_records('logstore_lanalytics_log', []));
        $testweekresult = query_helper::query_heatmap($course->id);
        var_dump($testweekresult);
        $this->assertEquals(3, $testweekresult[0]->value);
        $this->assertEquals(2, $testweekresult[100]->value);
        $this->assertEquals(2, $testweekresult[39]->value);
        $this->assertEquals(1, $testweekresult[17]->value);
        $this->assertEquals(false, array_key_exists(168, $testweekresult));
    }
}