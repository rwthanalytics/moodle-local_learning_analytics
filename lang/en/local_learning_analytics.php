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
 * Strings for local_learning_analytics
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Learning Analytics';

$string['learning_analytics'] = 'Learning Analytics';

$string['subplugintype_lareport'] = 'Report';
$string['subplugintype_lareport_plural'] = 'Reports';
$string['subplugintype_laoutput'] = 'Output';
$string['subplugintype_laoutput_plural'] = 'Outputs';


$string['show_full_list'] = 'Expand list';

// Terms also used by subplugins
$string['learners'] = 'Learners';
$string['sessions'] = 'Sessions';
$string['hits'] = 'Hits'; // "Aufrufe"

// Settings
$string['setting_status_description'] = 'This value sets whether the user interface should be activated and whether a links should be shown in the navigation. By default, the link and page are visible if logging is enabled for the course. You can use this option, if you want to enabled logging in all courses, but only want to enable the user interface on specific courses.';
$string['setting_status_option_show_if_enabled'] = 'Show navigation link if logging is enabled for the course';
$string['setting_status_option_show_courseids'] = 'Show navigation link if course is specified below via course_ids';
$string['setting_status_option_show_always'] = 'Show navigation link for all courses, even if logging is disabled for the course (only enable this, if you already logged data before)';
$string['setting_status_option_hide_link'] = 'Hide navigation link but keep the page enabled for all courses (only if you know the link, you can access the page)';
$string['setting_status_option_disable'] = 'Hide navigation link and disable the page for all courses';
$string['setting_status_course_customfield'] = 'Add option to the course settings so that teachers can enable it on their own';

$string['setting_course_ids_description'] = 'Use this option, when you select the second option above to decide for which courses the user interface should be enabled.';

$string['dataprivacy_threshold_description'] = 'This value determines how many "data points" a data set needs to contain before the data is displayed.';
$string['allow_dashboard_compare_description'] = 'Activate this options, to allow teachers to compare their course with another one of their courses in the dashboad. The option adds a link to the dashboard allowing the teachers to select another one of their courses. After selecting another course, the week plot will show a dashed line in the background in addition to the current course.';
$string['navigation_position_beforekey_description'] = 'Allows to specify where in the course navigation the link to the page is added. By default, the link is added before the first "section" node. Example value: <code>grades</code> to be shown before the link to grades. You can find the key of a node in the navigation by using the developer tools. Right-click on a link in the navigation, press <em>Inspect</em> and check the attribute <code>data-key</code> of the corresponding <code>a</code> element.';
$string['setting_student_rolenames_description'] = 'In case the role(s) for students/users in a course is not <code>student</code>, you can specify the corresponding role name. In case there are multiple roles that students have, use a single comma. Example: <code>student,customrole</code>';

$string['setting_student_enrols_groupby_description'] = 'This option defines which courses are merged together in the "Previous/Parallel course.';

// Help
$string['help_title'] = 'Help';
$string['help_take_tour'] = 'Take the interactive tour';
$string['help_text'] = 'Learning Analytics shows you the key metrics of your course.

The Learning Analytics displays data that was collected by itself as well as data from the Moodle course itself. All collected data is collected fully anonymously and cannot be traced back to users.

There are multiple metrics that are displayed. The most important one is the dashboard. From the dashboard there are 4 links that can provided more information.';

$string['report_coursedashboard_title'] = 'Dashboard (main page)';
$string['report_coursedashboard_description'] = 'The reports gives an overview of ...';
$string['report_learners_title'] = 'Registered users';
$string['report_learners_description'] = 'The reports gives an overview of ...';
$string['report_weekheatmap_title'] = 'Number of hits / Weekly heatmap';
$string['report_weekheatmap_description'] = 'The reports gives an overview of ...';
$string['report_quiz_assign_title'] = 'Registered users';
$string['report_quiz_assign_description'] = 'The reports gives an overview of ...';
$string['report_activities_title'] = 'Activities';
$string['report_activities_description'] = 'The reports gives an overview of ...';

$string['help_faq'] = 'Frequently Asked Questions (FAQ)';

$string['help_faq_week_start_question'] = 'Why does the first week in the dashboard graph not correspond to the actual start of the lecture?';
$string['help_faq_week_start_answer'] = 'The visualization in the dashboard depends on the setting "Course start date" in the course settings. If the date specified there does not correspond to the actual start of the lecture, the display in the dashboard will not be correct either.
If this is your course, you can correct the setting on the following page (General / Course start date):';

$string['help_faq_data_storage_question'] = 'Which data is stored and displayed by the service?';
$string['help_faq_data_storage_answer'] = 'The data presented is from two data sources.
Both sources are stored in the internal Moodle database.
The first data source is the internal Moodle database, such as the number of students in the course (left box in the dashboard).
Some of these data sets can be viewed on other Moodle pages and are just visualized differently by the Learning Analytics service.
The second data source is data that is collected specifically for the Learning Analytics service.
All data is stored anonymously.
The collected data does not allow to trace back to individual users.
Specifically, the following data is stored with each request in Moodle:';
$string['help_faq_data_storage_answer_list'] = 'Type of action (e.g. "Quiz attempt started") stored as number
Time (exact to the second)
Corresponding course (ID of the course) in which the action happened
Corresponding context (e.g. the ID of the quiz that was started)
Operating system and browser (e.g. "Windows 10" and "Firefox") stored as number (detailed browser or operating system versions are not stored)';

$string['help_faq_privacy_threshold_question'] = 'Why do some values shown as "< {$a}"?';
$string['help_faq_privacy_threshold_answer'] = 'For data privacy reasons, aggregated data is only displayed if at least {$a} data points are present.';

$string['help_faq_visibility_question'] = 'Who can access the Learning Analytics statistics?';
$string['help_faq_visibility_answer'] = 'To ensure maximum transparency, the displayed data can be viewed by the course owners as well as by the participants of the course.';

$string['help_faq_developer_question'] = 'Who develops the Learning Analytics service and where can I get further information?';
$string['help_faq_developer_answer'] = 'The Learning Analytics service is developed by RWTH Aachen University.
The development is Open Source. Therefore you can check the used algorithms yourself. On the following pages you can access further information:';

$string['tour_overview_title'] = 'Learning Analytics';
$string['tour_dashboard_boxes'] = 'The boxes at the bottom show the key metrics of your course. By clicking on the individual links you can get more information.';
$string['tour_more_information'] = 'TODO ...

If you need more information, check out the Help page.';

