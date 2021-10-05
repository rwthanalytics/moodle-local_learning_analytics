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

        $datagenerator = $this->getDataGenerator();
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics');

        $user = $datagenerator->create_user();
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $datagenerator->enrol_user($user->id, $course->id);

        $forum = $activitygenerator->create_instance(array('course' => $course->id));

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
        $contextinstance = $DB->get_record_sql($instancequery, [$forum->id]);
        $instanceid = $contextinstance->id;
        $context = $DB->get_record_sql($contextquery, [$instanceid]);
        $contextid = $context->id;

        for($i=0; $i<16; $i++) {
            $event = report_viewed::create(array(
                'contextid' => $contextid,
                'objectid' => NULL
            ));
            $event->add_record_snapshot('forum', $forum);
            $event->trigger();
        }
         
        $testresult1 = query_helper::query_activities($course->id,"" , []);

        $this->assertEquals(17, $testresult1[$instanceid]->hits);

        //second tests
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $activitygenerator->create_instance(array('course' => $course->id));

        $contextinstance2 = $DB->get_record_sql($instancequery, [$quiz->id]);
        $instanceid2 = $contextinstance2->id;
        $context2 = $DB->get_record_sql($contextquery, [$instanceid2]);
        $contextid2 = $context2->id;

        for($i=0; $i<10; $i++) {
            $event = report_viewed::create(array(
                'contextid' => $contextid2,
                'objectid' => NULL
            ));
            $event->add_record_snapshot('quiz', $quiz);
            $event->trigger();
        }
        
        $testresult2 = query_helper::query_activities($course->id,"" , []);

        $this->assertEquals(11, $testresult2[$instanceid2]->hits);
    }

    public function test_preview_query_most_clicked_activity() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();
        $activitygenerator = $this->getDataGenerator()->get_plugin_generator('mod_forum');

        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics');

        $user = $datagenerator->create_user();
        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $datagenerator->enrol_user($user->id, $course->id);

        $forum = $activitygenerator->create_instance(array('course' => $course->id));

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
        $contextinstance = $DB->get_record_sql($instancequery, [$forum->id]);
        $instanceid = $contextinstance->id;
        $context = $DB->get_record_sql($contextquery, [$instanceid]);
        $contextid = $context->id;

        $date = new \DateTime();
        $date->setTime(23, 59, 59); // Include today.
        $date->modify('-1 week');
        $oneweekago = $date->getTimestamp();
        $timestampBefore = $oneweekago - 40000;
        $timestampAfter = $oneweekago + 40000;

        $counterAfter = 0;
        $counterBefore = 0;
        for($i=0; $i<99; $i++) {
            $counterAfter++;
            $entry = [
                'id' => $counterAfter,
                'eventid' => 30,
                'timecreated' => $timestampAfter,
                'courseid' => $course->id,
                'contextid' => $contextid,
                'device' => 3611
            ];
            $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
            if($i%3==0) {
                $counterBefore++;
                $entry = [
                    'id' => $counterBefore,
                    'eventid' => 30,
                    'timecreated' => $counterBefore,
                    'courseid' => $course->id,
                    'contextid' => $contextid,
                    'device' => 3611
                ];
                $DB->insert_record('logstore_lanalytics_log', $entry, false, false, true);
            }
        }
         
        $testresult1 = query_helper::preview_query_most_clicked_activity($course->id, 1);

        $this->assertEquals(100, $testresult1[$instanceid]->hits);
    }
}
