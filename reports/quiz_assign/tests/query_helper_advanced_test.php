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
        foreach($quizids as $id) {
            $entry = [
                'id' => $i,
                'quiz' => $id->id,
                'userid' => $user->id,
                'attempt' => 1,
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

        $DB->set_field('quiz', 'sumgrades', 1, ['id' => array_pop($quizids)->id]);
        $DB->set_field('quiz', 'sumgrades', 1, ['id' => array_pop($quizids)->id]);
        $DB->set_field('quiz', 'sumgrades', 1, ['id' => array_pop($quizids)->id]);

        $testresult1 = query_helper::query_quiz($course->id);

        $this->assertEquals(2, array_pop($testresult1)->attempts);
        $this->assertEquals(2, array_pop($testresult1)->attempts);
        $this->assertEquals(2, array_pop($testresult1)->attempts);
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
        foreach($quizids as $id) {
            $aentry = [
                'id' => $i,
                'course' => $course->id,
                'name' => 'test',
                'intro' => 'test',
                'introformat' => 0,
                'alwaysshowdescription' => 0,
                'nosubmissions' => 0,
                'submissiondrafts' => 0,
                'sendnotifications' => 0,
                'sendlatenotifications' => 0,
                'duedate' => 0,
                'allowsubmissionsfromdate' => 0,
                'grade' => 1,
                'timemodified' => $oneweekago + 200,
                'requiresubmissionstatement' => 0,
                'completionsubmit' => 0,
                'cutoffdate' => 0,
                'gradingduedate' => 0,
                'teamsubmission' => 0,
                'requireallteammemberssubmit' => 0,
                'teamsubmissiongroupingid' => 0,
                'blindmarking' => 0,
                'hidegrader' => 0,
                'revealidentities' => 0,
                'attemptreopenmethod' => 't',
                'maxattempts' => -1,
                'markingworkflow' => 0,
                'sendstudentnotifications' => 1,
                'preventsubmissionnotingroup' => 0
            ];
            $DB->insert_record('assign', $aentry, false, false, true);
            $i++;
        }

        $refquery1 = <<<SQL
        SELECT
            id
        FROM {assign} As a
        WHERE a.name = 'test'
SQL;

        $assignids = $DB->get_records_sql($refquery1, []);

        $i = 1;

        foreach($assignids as $assignid) {
            $gientry = [
                'id' => $i,
                'courseid' => $course->id,
                'categoryid' => NULL,
                'itemname' => 'test',
                'itemtype' => 'mod',
                'itemmodule' => 'assign',
                'iteminstance' => $assignid->id,
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
            $i++;
        }

        $refquery2 = <<<SQL
        SELECT
            id
        FROM {grade_items} As gi
        WHERE gi.itemname = 'test'
SQL;

        $giids = $DB->get_records_sql($refquery2, []);

        $i = 1;

        foreach($giids as $giid) {
            $ggentry = [
                'id' => $i,
                'itemid' => $giid->id,
                'userid' => $user->id,
                'rawgrade' => $i * 2,
                'rawgrademax' => '10.00000',
                'rawgrademin' => '1.00000',
                'rawscaleid' => $i,
                'usermodified' => $oneweekago + 200,
                'finalgrade' => '2.00000',
                'hidden' => 0,
                'locked' => 0,
                'locktime' => 0,
                'exported' => 0,
                'feedback' => null,
                'feedbackformat' => 0,
                'information' => null,
                'informationformat' => 0,
                'timecreated' => $oneweekago,
                'timemodified' => $oneweekago + 200,
                'aggregationstatus' => 0,
                'aggregationweight' => 0
            ];
            $DB->insert_record('grade_grades', $ggentry, false, false, true);
            $i++;
        }

        $aquery = <<<SQL
        SELECT *
        FROM {assign}
SQL;

        $giquery = <<<SQL
        SELECT *
        FROM {grade_items}
SQL;

        $ggquery = <<<SQL
        SELECT *
        FROM {grade_grades}
SQL;

        $testresult1 = query_helper::query_assignment($course->id);

        $this->assertEquals(5/9, array_pop($testresult1)->grade);
        $this->assertEquals(3/9, array_pop($testresult1)->grade);
        $this->assertEquals(1/9, array_pop($testresult1)->grade);
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

        $query = <<<SQL
        SELECT
            id
        FROM {quiz}
SQL;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $today = $date->getTimestamp();
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $date->modify('-1 week');
        $twoweeksago = $date->getTimestamp();

        $quizids = $DB->get_records_sql($query);
        $i=1;
        foreach($quizids as $id) {
            $aentry = [
                'id' => $i,
                'course' => $course->id,
                'name' => 'test',
                'intro' => 'test',
                'introformat' => 0,
                'alwaysshowdescription' => 0,
                'nosubmissions' => 0,
                'submissiondrafts' => 0,
                'sendnotifications' => 0,
                'sendlatenotifications' => 0,
                'duedate' => 0,
                'allowsubmissionsfromdate' => 0,
                'grade' => 1,
                'timemodified' => $oneweekago + 200,
                'requiresubmissionstatement' => 0,
                'completionsubmit' => 0,
                'cutoffdate' => 0,
                'gradingduedate' => 0,
                'teamsubmission' => 0,
                'requireallteammemberssubmit' => 0,
                'teamsubmissiongroupingid' => 0,
                'blindmarking' => 0,
                'hidegrader' => 0,
                'revealidentities' => 0,
                'attemptreopenmethod' => 't',
                'maxattempts' => -1,
                'markingworkflow' => 0,
                'sendstudentnotifications' => 1,
                'preventsubmissionnotingroup' => 0
            ];
            $DB->insert_record('assign', $aentry, false, false, true);
            $asentry = [
                'id' => $i,
                'assignment' => $i,
                'userid' => $user->id,
                'timecreated' => $oneweekago + 1000,
                'timemodified' => $oneweekago + 1000,
                'status' => 'submitted',
                'groupid' => 0,
                'attemptnumber' => 0,
                'latest' => 0
            ];
            $DB->insert_record('assign_submission', $asentry, false, false, true);
            $qaentry = [
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
                'timecheckstate' => 0,
                'sumgrades' => 1/$i
            ];
            $DB->insert_record('quiz_attempts', $qaentry, false, false, true);
            $i = $i + 1;
        }

        $aentry = [
            'id' => $i,
            'course' => $course->id,
            'name' => 'test',
            'intro' => 'test',
            'introformat' => 0,
            'alwaysshowdescription' => 0,
            'nosubmissions' => 0,
            'submissiondrafts' => 0,
            'sendnotifications' => 0,
            'sendlatenotifications' => 0,
            'duedate' => 0,
            'allowsubmissionsfromdate' => 0,
            'grade' => 1,
            'timemodified' => $twoweeksago + 200,
            'requiresubmissionstatement' => 0,
            'completionsubmit' => 0,
            'cutoffdate' => 0,
            'gradingduedate' => 0,
            'teamsubmission' => 0,
            'requireallteammemberssubmit' => 0,
            'teamsubmissiongroupingid' => 0,
            'blindmarking' => 0,
            'hidegrader' => 0,
            'revealidentities' => 0,
            'attemptreopenmethod' => 't',
            'maxattempts' => -1,
            'markingworkflow' => 0,
            'sendstudentnotifications' => 1,
            'preventsubmissionnotingroup' => 0
        ];
        $DB->insert_record('assign', $aentry, false, false, true);
        $asentry = [
            'id' => $i,
            'assignment' => $i,
            'userid' => $user->id,
            'timecreated' => $twoweeksago + 1000,
            'timemodified' => $twoweeksago + 1000,
            'status' => 'submitted',
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 0
        ];
        $DB->insert_record('assign_submission', $asentry, false, false, true);
        $qaentry = [
            'id' => $i,
            'quiz' => $id->id,
            'userid' => $user->id,
            'attempt' => $i * 2,
            'uniqueid' => $i,
            'layout' => '1,0',
            'currentpage' => 0,
            'preview' => 1,
            'state' => 'finished',
            'timestart' => $twoweeksago,
            'timefinish' => $twoweeksago + 200,
            'timemodified' => $twoweeksago + 200,
            'timemodifiedoffline' => 0,
            'timecheckstate' => 0,
            'sumgrades' => 1/$i
        ];
        $DB->insert_record('quiz_attempts', $qaentry, false, false, true);

        $testresult1 = query_helper::preview_quiz_and_assigments($course->id, 1);

        $this->assertEquals([1, 3], $testresult1);
    }
}
