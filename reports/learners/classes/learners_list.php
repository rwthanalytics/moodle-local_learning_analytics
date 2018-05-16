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
 *
 *
 * @package     local_learning_analytics
 * @copyright   2018 Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @author      Marcel Behrmann <behrmann@lfi.rwth-aachen.de>
 * @author      Thomas Dondorf <dondorf@lfi.rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lareport_learners;

use local_learning_analytics\local\outputs\table;
use lareport_learners\query_helper;
use local_learning_analytics\local\routing\router;
use moodle_url;
use paging_bar;

defined('MOODLE_INTERNAL') || die;

class learners_list {

    private static $USER_PER_FULL_PAGE = 20;
    private static $USER_PREVIEW = 5;

    public static function generate(int $courseid, int $page = -1, string $roleFilter = ''): array {

        $fullPage = true;
        $perPage = self::$USER_PER_FULL_PAGE;

        if ($page === -1) {
            $fullPage = false;
            $page = 0;
            $perPage = self::$USER_PREVIEW;
        }

        $learnersCount = query_helper::query_learners_count($courseid, $roleFilter);

        $learners = query_helper::query_learners($courseid, $roleFilter, $page * $perPage, $perPage);
        $table = new table();
        $table->set_header_local(['firstname', 'lastname', 'role', 'firstaccess', 'lastaccess', 'hits', 'sessions'],
            'lareport_learners');

        $maxHits = reset($learners)->hits;
        $maxSessions = 1;
        foreach ($learners as $learner) {
            $maxSessions = max($maxSessions, $learner->sessions);
        }

        foreach ($learners as $learner) {
            $firstaccess = !empty($learner->firstaccess) ? userdate($learner->firstaccess) : '-';
            $lastaccess = !empty($learner->lastaccess) ? userdate($learner->lastaccess) : '-';

            $table->add_row([
                $learner->firstname,
                $learner->lastname,
                $learner->role,
                $firstaccess,
                $lastaccess,
                table::fancyNumberCell(
                    (int) $learner->hits,
                    $maxHits,
                    'green'
                ),
                table::fancyNumberCell(
                    (int) $learner->sessions,
                    $maxSessions,
                    'red'
                )
            ]);
        }

        if ($fullPage) {
            $pageParams = ['course' => $courseid];
            if ($roleFilter !== '') {
                $pageParams['role'] = $roleFilter;
            }
            $pageUrl = new moodle_url('/local/learning_analytics/index.php/reports/learners/all', $pageParams);
            $pagingbar = new paging_bar($learnersCount, $page, $perPage, $pageUrl);

            return [$pagingbar, $table, $pagingbar];
        } else {
            $linkToFullList = router::report_page('learners', 'all', ['course' => $courseid]);
            $table->add_show_more_row($linkToFullList);

            return [$table];
        }

    }
}