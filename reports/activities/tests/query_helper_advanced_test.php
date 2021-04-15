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

use lareport_activities\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
require_once(__DIR__ . '/../../../../../config.php');

//use: navigate in cmd to your moodle folder and enter vendor\bin\phpunit local_Learning_Analytics_reports_activities_testcase local\Learning_Analytics\reports\activities\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_activities_testcase extends \advanced_testcase {

    public function test_activities() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $user = $this->getDataGenerator()->create_user();
        $category = $this->getDataGenerator()->create_category();
        $course1 = $this->getDataGenerator()->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $course2 = $this->getDataGenerator()->create_course(array('name'=>'sametimetestcourse', 'category'=>$category->id));
        $course3 = $this->getDataGenerator()->create_course(array('name'=>'beforetimetestcourse', 'category'=>$category->id, 'timecreated'=>(time() - (7 * 24 * 60 * 60 * 180))));
        $this->getDataGenerator()->enrol_user($user->id, $course1->id);
        $this->getDataGenerator()->enrol_user($user->id, $course2->id);
        $this->getDataGenerator()->enrol_user($user->id, $course3->id);

        $testresult1 = query_helper::query_activities($course1->id);
        $testresult2 = query_helper::query_activities($course2->id);
        $testresult3 = query_helper::query_activities($course3->id);
        var_dump($testresult1);

        $this->assertEquals(1, 1);
    }
}
