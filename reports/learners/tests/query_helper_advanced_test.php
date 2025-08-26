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

use lareport_learners\query_helper;
use PHPUnit\Framework\TestCase;
use local_learning_analytics\event\report_viewed;
use local_learning_analytics\report_list;
use local_learning_analytics\settings;
require_once(__DIR__ . '/../../../../../config.php');

//use: navigate in cmd to your moodle folder and enter vendor\bin\phpunit local_Learning_Analytics_reports_learners_testcase local\Learning_Analytics\reports\learners\tests\query_helper_advanced_test.php
class local_Learning_Analytics_reports_learners_testcase extends \advanced_testcase {

    public function test_learners_count_role_unspecific() {
        
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();

        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        for($i=0; $i<17; $i++) {
            $user = $datagenerator->create_user();
            $datagenerator->enrol_user($user->id, $course->id);
        }

        $this->assertEquals(17, query_helper::query_learners_count($course->id, ['student']));
    }

    public function test_learners_count_role_specific() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();

        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        for($i=0; $i<13; $i++) {
            $user = $datagenerator->create_user();
            $datagenerator->enrol_user($user->id, $course->id, 'student');
        }
        for($i=0; $i<15; $i++) {
            $user = $datagenerator->create_user();
            $datagenerator->enrol_user($user->id, $course->id, 'teacher');
        }

        $this->assertEquals(13, query_helper::query_learners_count($course->id, ['student']));
        $this->assertEquals(15, query_helper::query_learners_count($course->id, ['teacher']));
    }
    
    public function test_courseparticipation() {
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();

        $parallelcoursebuffer = 31 * 24 * 60 * 60;
        $privacythreshold = settings::get_config('dataprivacy_threshold');
        $studentrolenames = explode(',', settings::get_config('student_rolenames'));
        $coursegroupby = 'id';
        
        $category = $datagenerator->create_category();
        $course1  = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $coursestartdate = get_course($course1->id)->startdate;
        $coursebeforecutoff = $coursestartdate - $parallelcoursebuffer;
        $courseparallelcutoff = $coursestartdate + $parallelcoursebuffer;
        $course1id = intval($course1->id);
        $course2  = $datagenerator->create_course(array('name'=>'sametimetestcourse', 'category'=>$category->id));
        $course3  = $datagenerator->create_course(array('name'=>'beforetimetestcourse', 'category'=>$category->id, 'timecreated'=>($coursestartdate - (7 * 24 * 60 * 60 * 180))));     
        $course4  = $datagenerator->create_course(array('name'=>'testcourse2', 'category'=>$category->id));
        $course5  = $datagenerator->create_course(array('name'=>'testcourse3', 'category'=>$category->id));
        $course6  = $datagenerator->create_course(array('name'=>'testcourse4', 'category'=>$category->id));
        $course7  = $datagenerator->create_course(array('name'=>'testcourse5', 'category'=>$category->id));
        $course8  = $datagenerator->create_course(array('name'=>'testcourse6', 'category'=>$category->id));
        $course9  = $datagenerator->create_course(array('name'=>'testcourse7', 'category'=>$category->id));
        $course10 = $datagenerator->create_course(array('name'=>'testcourse8', 'category'=>$category->id));
        $course11 = $datagenerator->create_course(array('name'=>'testcourse9', 'category'=>$category->id));
        $course12 = $datagenerator->create_course(array('name'=>'testcourse10', 'category'=>$category->id));
        $course13 = $datagenerator->create_course(array('name'=>'aftertimetestcourse', 'category'=>$category->id, 'timecreated'=>($coursestartdate + (7 * 24 * 60 * 60 * 180))));

        $users = [];
        for($i=0; $i<$privacythreshold+1; $i++) {
            $users[$i] = $datagenerator->create_user();
            $datagenerator->enrol_user($users[$i]->id, $course1->id);
            $datagenerator->enrol_user($users[$i]->id, $course2->id);
            $datagenerator->enrol_user($users[$i]->id, $course3->id);
            $datagenerator->enrol_user($users[$i]->id, $course4->id);
            $datagenerator->enrol_user($users[$i]->id, $course5->id);
            $datagenerator->enrol_user($users[$i]->id, $course6->id);
            $datagenerator->enrol_user($users[$i]->id, $course7->id);
            $datagenerator->enrol_user($users[$i]->id, $course8->id);
            $datagenerator->enrol_user($users[$i]->id, $course9->id);
            $datagenerator->enrol_user($users[$i]->id, $course10->id);
            $datagenerator->enrol_user($users[$i]->id, $course11->id);
            $datagenerator->enrol_user($users[$i]->id, $course12->id);
            $datagenerator->enrol_user($users[$i]->id, $course13->id);
        }

        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_lanalytics', 'tool_log');
        set_config('buffersize', 0, 'logstore_lanalytics');

        $testresult1 = query_helper::query_courseparticipation($course1id, $privacythreshold, $studentrolenames, $coursebeforecutoff, $courseparallelcutoff, $coursegroupby);
        $this->assertEquals(COUNT($testresult1), 12);
    }

    public function test_localization() {
        
        global $DB, $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $datagenerator = $this->getDataGenerator();

        $category = $datagenerator->create_category();
        $course = $datagenerator->create_course(array('name'=>'testcourse', 'category'=>$category->id));
        $courseid = $course->id;
        $type = 'lang';
        for($i=0; $i<13; $i++) {
            $user = $datagenerator->create_user();
            $datagenerator->enrol_user($user->id, $course->id, 'student');
        }
        for($i=0; $i<15; $i++) {
            $user = $datagenerator->create_user();
            $datagenerator->enrol_user($user->id, $course->id, 'teacher');
        }

        $testresult1 = query_helper::query_localization($courseid, $type);

        $this->assertEquals(13, $testresult1["en"]->users);
    }
}
