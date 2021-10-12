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
        $startdatezone = new \DateTimeZone('Europe/Berlin');
        $startdate = new DateTime("now", $startdatezone);
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

        $testweekresult = query_helper::query_heatmap($course->id);

        $get_arrayname = function($val) {
            /*$myzone = new \DateTimeZone('Europe/Berlin');
            $refzone = new \DateTimeZone('UTC');
            $dateTimeMy = new DateTime("now", $myzone);
            $dateTimeRef = new DateTime("now", $refzone);
            $val = $val - (($dateTimeMy->getOffset() + $dateTimeRef->getOffset()) / 3600);
            if($val<0) {
                $val = $val + 168;
            }*/
            $returner = '' . floor($val/24) . '-' . floor($val%24);
            return $returner;
        };

        $query = <<<SQL
        SELECT timecreated
        FROM {logstore_lanalytics_log}
SQL;

        //$this->assertEquals(3, $testweekresult[$get_arrayname(0)]->value);
        //$this->assertEquals(2, $testweekresult[$get_arrayname(100)]->value);
        //$this->assertEquals(2, $testweekresult[$get_arrayname(39)]->value);
        //$this->assertEquals(1, $testweekresult[$get_arrayname(17)]->value);
        $this->assertEquals(false, array_key_exists(168, $testweekresult));
    }

    public function test_preview_query_click_count() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $datagenerator = $this->getDataGenerator();
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics');
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $date = new \DateTime();
        $date->setTime(23, 59, 59);
        $today = $date->getTimestamp();
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $counterThisWeek = 0;
        $counterOneWeeksAgo = 0;
        for($i=0; $i<99; $i++) {
            $counterThisWeek++;
            $entry = [
                'id' => $counterThisWeek,
                'eventid' => 30,
                'timecreated' => $today - 40000,
                'courseid' => $course->id,
                'contextid' => 46,
                'device' => 3611
            ];
            $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
            if($i%2==0) {
                $counterOneWeeksAgo++;
                $entry = [
                    'id' => $counterOneWeeksAgo,
                    'eventid' => 30,
                    'timecreated' => $oneweekago - 40000,
                    'courseid' => $course->id,
                    'contextid' => 46,
                    'device' => 3611
                ];
                $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
            }
        }
        $testweekresult = query_helper::preview_query_click_count($course->id);

        $this->assertEquals(50, $testweekresult["hits"][0]);
        $this->assertEquals(109, $testweekresult["hits"][1]);
    }
}