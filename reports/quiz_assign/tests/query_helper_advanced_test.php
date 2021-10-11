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

use lareport_quiz_assign\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
require_once(__DIR__ . '/../../../../../config.php');

//use: navigate in cmd to your moodle folder and enter vendor\bin\phpunit local_Learning_Analytics_reports_quiz_assign_testcase local\Learning_Analytics\reports\quiz_assign\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_quiz_assign_testcase extends \advanced_testcase {

    public function test_query_quiz() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        $user = $datagenerator->create_user();
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $datagenerator->enrol_user($user->id, $course->id);

        $quiz1 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz2 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz3 = $activitygenerator->create_instance(array('course' => $course->id));

        $query = <<<SQL
        SELECT
            id
        FROM {quiz}
SQL;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $quizids = $DB->get_records_sql($query);
        $i=1;
        var_dump($i);
        foreach($quizids as $id) {
            $entry = [
                'id' => $i,
                'quiz' => $id->id,
                'userid' => $user->id,
                'attempt' => $i * 2,
                'uniqueid' => $i,
                'layout' => '1,0',
                'currentpage' => 0,
                'preview' => 1,
                'state' => 'finished',
                'timestart' => $oneweekago,
                'timefinish' => $oneweekago + 200,
                'timemodified' => $oneweekago + 200,
                'timemodifiedoffline' => 0,
                'timecheckstate' => NULL,
                'sumgrades' => 1/$i
            ];
            $DB->insert_record('quiz_attempts', $entry, false, false, true);
            $gientry = [
                'id' => $i,
                'courseid' => $course->id,
                'categoryid' => NULL,
                'itemname' => 'test',
                'itemtype' => 'mod',
                'itemmodule' => 'quiz',
                'iteminstance' => $id->id,
                'itemnumber' => 0,
                'iteminfo' => NULL,
                'idnumber' => NULL,
                'calculation' => NULL,
                'gradetype' => 1,
                'grademax' => '10.00000',
                'grademin' => '0.00000',
                'scaleid' => NULL,
                'outcomeid' => NULL,
                'gradepass' => '0.00000',
                'plusfactor' => '0.00000',
                'aggregationcoef' => '0.00000',
                'aggregationcoef2' => '0.00000',
                'sortorder' => $i,
                'display' => 0,
                'deciamals' => NULL,
                'hidden' => 0,
                'locked' => 0,
                'locktime' => 0,
                'needsupdate' => 0,
                'weightoverride' => 0,
                'timecreated' => $oneweekago,
                'timemodified' => $oneweekago + 200
            ];
            $DB->insert_record('grade_items', $gientry, false, false, true);
            $i = $i + 1;
        }

        $testresult1 = query_helper::query_quiz($course->id);
        var_dump($testresult1);

        $this->assertEquals(1, 1);
    }

    public function test_query_assignment() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        $user = $datagenerator->create_user();
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $datagenerator->enrol_user($user->id, $course->id);

        $quiz1 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz2 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz3 = $activitygenerator->create_instance(array('course' => $course->id));

        $instancequery = <<<SQL
        SELECT
            id
        FROM {course_modules} AS cm
        WHERE instance = ?
SQL;

        $contextquery = <<<SQL
        SELECT
            id
        FROM {context} AS c
        WHERE instanceid = ?
SQL;

        $logmanager = get_log_manager(true);
        $contextinstance = $DB->get_record_sql($instancequery, [$quiz1->id]);
        $instanceid = $contextinstance->id;
        $context = $DB->get_record_sql($contextquery, [$instanceid]);
        $contextid = $context->id;

        $this->assertEquals(1, 1);
    }

    public function test_preview_quiz_and_assigments() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');

        $user = $datagenerator->create_user();
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $datagenerator->enrol_user($user->id, $course->id);

        $quiz1 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz2 = $activitygenerator->create_instance(array('course' => $course->id));
        $quiz3 = $activitygenerator->create_instance(array('course' => $course->id));

        $instancequery = <<<SQL
        SELECT
            id
        FROM {course_modules} AS cm
        WHERE instance = ?
SQL;

        $contextquery = <<<SQL
        SELECT
            id
        FROM {context} AS c
        WHERE instanceid = ?
SQL;

        $logmanager = get_log_manager(true);
        $contextinstance = $DB->get_record_sql($instancequery, [$quiz1->id]);
        $instanceid = $contextinstance->id;
        $context = $DB->get_record_sql($contextquery, [$instanceid]);
        $contextid = $context->id;

        $this->assertEquals(1, 1);
    }
}
