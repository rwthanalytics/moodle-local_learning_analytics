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

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

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

        $testweekresult = query_helper::query_heatmap($course->id);
        $testweek = end($testweekresult);
        $testweekclicks = $testweek->clicks;

        $this->assertEquals(1, $testweekclicks);
    }
}
