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
$string['dataprivacy_threshold'] = 'dataprivacy_threshold';
$string['dataprivacy_threshold_description'] = 'This value determines how many "data points" a data set needs to contain before the data is displayed.';
$string['allow_dashboard_compare'] = 'allow_dashboard_compare';
$string['allow_dashboard_compare_description'] = 'Activate this options, to allow teachers to compare their course with another one of their courses in the dashboad. The option adds a link to the dashboard allowing the teachers to select another one of their courses. After selecting another course, the week plot will show a dashed line in the background in addition to the current course.';
$string['navigation_position_beforekey'] = 'navigation_position_beforekey';
$string['navigation_position_beforekey_description'] = 'Allows you to specify where in the course navigation the link to the page is added. By default, the link is added before the first "section" node. Example value: <code>grades</code> to be shown before the link to grades. You can find the key of a node in the navigation by using the developer tools. Right-click on a link in the navigation, press <em>Inspect</em> and check the attribute <code>data-key</code> of the corresponding <code>a</code> element.';
