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
 * Learning Analytics Demo Data
 *
 * @package     local_learning_analytics
 * @copyright   Lehr- und Forschungsgebiet Ingenieurhydrologie - RWTH Aachen University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learning_analytics;

defined('MOODLE_INTERNAL') || die;

// These numbers are all made up for demo purposes (documentation, screenshots)

class demo {

    static private $demodata = [];

    static public function data($page, $component) {
        if (empty(self::$demodata[$page][$component])) {
            throw new Error('Demo Data not found: ' . $page . ' / ' . $component);
        }
        return self::$demodata[$page][$component];
    }

    static public function setup() {
        self::$demodata = [

            /*--************************************************ Dashboard */

            'coursedashboard' => [
                'plot_weeks' => 14,
                'plot' => [
                    -2 => (object) [ 'clicks' => 2 ],
                    -1 => (object) [ 'clicks' => 8 ],
                    0 => (object) [ 'clicks' => 12 ],
                    1  => (object) [ 'clicks' => 280 ],
                    2  => (object) [ 'clicks' => 329 ],
                    3  => (object) [ 'clicks' => 320 ],
                    4  => (object) [ 'clicks' => 682 ],
                    5  => (object) [ 'clicks' => 628 ],
                    6  => (object) [ 'clicks' => 387 ],
                    7  => (object) [ 'clicks' => 280 ],
                    8  => (object) [ 'clicks' => 321 ],
                    9  => (object) [ 'clicks' => 391 ],
                    10 => (object) [ 'clicks' => 310 ],
                    11 => (object) [ 'clicks' => 155 ],
                    12 => (object) [ 'clicks' => 123 ],
                    13 => (object) [ 'clicks' => 168 ],
                    14 => (object) [ 'clicks' => 248 ],
                    15 => (object) [ 'clicks' => 186 ],
                    16 => (object) [ 'clicks' => 131 ],
                    17 => (object) [ 'clicks' => 144 ],
                    18 => (object) [ 'clicks' => 54 ],
                    // 19 => (object) [ 'clicks' => 91 ],
                    // 20 => (object) [ 'clicks' => 118 ],
                    // 21 => (object) [ 'clicks' => 348 ],
                    // 22 => (object) [ 'clicks' => 509 ],
                    // 23 => (object) [ 'clicks' => 5234 ],
                    // 24 => (object) [ 'clicks' => 2817 ],
                    // 25 => (object) [ 'clicks' => 103 ],
                    // 26 => (object) [ 'clicks' => 12 ],
                    // 27 => (object) [ 'clicks' => 12 ],
                    // 28 => (object) [ 'clicks' => 14 ],
                    // 29 => (object) [ 'clicks' => 37 ],
                    // 30 => (object) [ 'clicks' => 13 ]
                ],
                'users' => [126, 0],
                'hits' => [ 'hits' => [ 180, 214 ]],
                'quiz_assign' => [3, 32],
                'activities' => [
                    7784 => (object) [
                        'cmid' => 7784,
                        'modname' => 'quiz',
                        'hits' => 52
                    ],
                    7790 => (object) [
                        'cmid' => 7790,
                        'modname' => 'resource',
                        'hits' => 41
                    ],
                    7780 => (object) [
                        'cmid' => 7780,
                        'modname' => 'resource',
                        'hits' => 24
                    ]
                ]
            ],

            /*--************************************************ Learners */

            'learners' => [
                'total' => 126,
                'overlap' => [
                    (object) [
                        'fullname' => 'Programmmierung',
                        'beforeparallel' => '1',
                        'users' => '120'
                    ],
                    (object) [
                        'fullname' => 'Einführung in die Technische Informatik',
                        'beforeparallel' => '1',
                        'users' => '118'
                    ],
                    (object) [
                        'fullname' => 'Einführung in die Softwaretechnik',
                        'beforeparallel' => '1',
                        'users' => '104'
                    ],
                    (object) [
                        'fullname' => 'Lineare Algebra',
                        'beforeparallel' => '1',
                        'users' => '76'
                    ],
                    (object) [
                        'fullname' => 'Datenbanken',
                        'beforeparallel' => '1',
                        'users' => '37'
                    ],
                    (object) [
                        'fullname' => 'Analysis I',
                        'beforeparallel' => '2',
                        'users' => '52'
                    ],
                    (object) [
                        'fullname' => 'Mathematische Logik',
                        'beforeparallel' => '2',
                        'users' => '34'
                    ],
                    (object) [
                        'fullname' => 'Praktikum Systemprogrammierung',
                        'beforeparallel' => '2',
                        'users' => '15'
                    ],
                    (object) [
                        'fullname' => 'Datenkommunikation und Sicherheit',
                        'beforeparallel' => '2',
                        'users' => '10'
                    ],
                ]
                ],

            /*--************************************************ Learners */
            'weekheatmap' => [
                'heatmap' => [
                    (object) [ 'heatpoint' => '0', 'value' => '46' ],
                    (object) [ 'heatpoint' => '1', 'value' => '123' ],
                    (object) [ 'heatpoint' => '2', 'value' => '73' ],
                    (object) [ 'heatpoint' => '3', 'value' => '36' ],
                    (object) [ 'heatpoint' => '4', 'value' => '12' ],
                    (object) [ 'heatpoint' => '5', 'value' => '23' ],
                    (object) [ 'heatpoint' => '6', 'value' => '50' ],
                    (object) [ 'heatpoint' => '7', 'value' => '6' ],
                    (object) [ 'heatpoint' => '8', 'value' => '96' ],
                    (object) [ 'heatpoint' => '9', 'value' => '161' ],
                    (object) [ 'heatpoint' => '10', 'value' => '539' ],
                    (object) [ 'heatpoint' => '11', 'value' => '1061' ],
                    (object) [ 'heatpoint' => '12', 'value' => '1189' ],
                    (object) [ 'heatpoint' => '13', 'value' => '954' ],
                    (object) [ 'heatpoint' => '14', 'value' => '874' ],
                    (object) [ 'heatpoint' => '15', 'value' => '798' ],
                    (object) [ 'heatpoint' => '16', 'value' => '762' ],
                    (object) [ 'heatpoint' => '17', 'value' => '834' ],
                    (object) [ 'heatpoint' => '18', 'value' => '698' ],
                    (object) [ 'heatpoint' => '19', 'value' => '556' ],
                    (object) [ 'heatpoint' => '20', 'value' => '207' ],
                    (object) [ 'heatpoint' => '21', 'value' => '574' ],
                    (object) [ 'heatpoint' => '22', 'value' => '604' ],
                    (object) [ 'heatpoint' => '23', 'value' => '505' ],
                    (object) [ 'heatpoint' => '24', 'value' => '218' ],
                    (object) [ 'heatpoint' => '25', 'value' => '169' ],
                    (object) [ 'heatpoint' => '26', 'value' => '81' ],
                    (object) [ 'heatpoint' => '27', 'value' => '47' ],
                    (object) [ 'heatpoint' => '28', 'value' => '17' ],
                    (object) [ 'heatpoint' => '29', 'value' => '58' ],
                    (object) [ 'heatpoint' => '30', 'value' => '8' ],
                    (object) [ 'heatpoint' => '31', 'value' => '45' ],
                    (object) [ 'heatpoint' => '32', 'value' => '14' ],
                    (object) [ 'heatpoint' => '33', 'value' => '69' ],
                    (object) [ 'heatpoint' => '34', 'value' => '271' ],
                    (object) [ 'heatpoint' => '35', 'value' => '689' ],
                    (object) [ 'heatpoint' => '36', 'value' => '872' ],
                    (object) [ 'heatpoint' => '37', 'value' => '886' ],
                    (object) [ 'heatpoint' => '38', 'value' => '933' ],
                    (object) [ 'heatpoint' => '39', 'value' => '1124' ],
                    (object) [ 'heatpoint' => '40', 'value' => '1086' ],
                    (object) [ 'heatpoint' => '41', 'value' => '877' ],
                    (object) [ 'heatpoint' => '42', 'value' => '1068' ],
                    (object) [ 'heatpoint' => '43', 'value' => '872' ],
                    (object) [ 'heatpoint' => '44', 'value' => '481' ],
                    (object) [ 'heatpoint' => '45', 'value' => '393' ],
                    (object) [ 'heatpoint' => '46', 'value' => '682' ],
                    (object) [ 'heatpoint' => '47', 'value' => '403' ],
                    (object) [ 'heatpoint' => '48', 'value' => '643' ],
                    (object) [ 'heatpoint' => '49', 'value' => '495' ],
                    (object) [ 'heatpoint' => '50', 'value' => '269' ],
                    (object) [ 'heatpoint' => '51', 'value' => '98' ],
                    (object) [ 'heatpoint' => '52', 'value' => '43' ],
                    (object) [ 'heatpoint' => '53', 'value' => '9' ],
                    (object) [ 'heatpoint' => '54', 'value' => '29' ],
                    (object) [ 'heatpoint' => '55', 'value' => '24' ],
                    (object) [ 'heatpoint' => '56', 'value' => '6' ],
                    (object) [ 'heatpoint' => '57', 'value' => '155' ],
                    (object) [ 'heatpoint' => '58', 'value' => '425' ],
                    (object) [ 'heatpoint' => '59', 'value' => '678' ],
                    (object) [ 'heatpoint' => '60', 'value' => '1315' ],
                    (object) [ 'heatpoint' => '61', 'value' => '1102' ],
                    (object) [ 'heatpoint' => '62', 'value' => '1695' ],
                    (object) [ 'heatpoint' => '63', 'value' => '2114' ],
                    (object) [ 'heatpoint' => '64', 'value' => '1852' ],
                    (object) [ 'heatpoint' => '65', 'value' => '2200' ],
                    (object) [ 'heatpoint' => '66', 'value' => '1272' ],
                    (object) [ 'heatpoint' => '67', 'value' => '376' ],
                    (object) [ 'heatpoint' => '68', 'value' => '389' ],
                    (object) [ 'heatpoint' => '69', 'value' => '233' ],
                    (object) [ 'heatpoint' => '70', 'value' => '370' ],
                    (object) [ 'heatpoint' => '71', 'value' => '176' ],
                    (object) [ 'heatpoint' => '72', 'value' => '165' ],
                    (object) [ 'heatpoint' => '73', 'value' => '105' ],
                    (object) [ 'heatpoint' => '74', 'value' => '63' ],
                    (object) [ 'heatpoint' => '75', 'value' => '112' ],
                    (object) [ 'heatpoint' => '76', 'value' => '36' ],
                    (object) [ 'heatpoint' => '77', 'value' => '25' ],
                    (object) [ 'heatpoint' => '78', 'value' => '21' ],
                    (object) [ 'heatpoint' => '79', 'value' => '44' ],
                    (object) [ 'heatpoint' => '80', 'value' => '148' ],
                    (object) [ 'heatpoint' => '81', 'value' => '332' ],
                    (object) [ 'heatpoint' => '82', 'value' => '510' ],
                    (object) [ 'heatpoint' => '83', 'value' => '766' ],
                    (object) [ 'heatpoint' => '84', 'value' => '601' ],
                    (object) [ 'heatpoint' => '85', 'value' => '570' ],
                    (object) [ 'heatpoint' => '86', 'value' => '445' ],
                    (object) [ 'heatpoint' => '87', 'value' => '530' ],
                    (object) [ 'heatpoint' => '88', 'value' => '559' ],
                    (object) [ 'heatpoint' => '89', 'value' => '647' ],
                    (object) [ 'heatpoint' => '90', 'value' => '432' ],
                    (object) [ 'heatpoint' => '91', 'value' => '390' ],
                    (object) [ 'heatpoint' => '92', 'value' => '371' ],
                    (object) [ 'heatpoint' => '93', 'value' => '157' ],
                    (object) [ 'heatpoint' => '94', 'value' => '117' ],
                    (object) [ 'heatpoint' => '95', 'value' => '171' ],
                    (object) [ 'heatpoint' => '96', 'value' => '161' ],
                    (object) [ 'heatpoint' => '97', 'value' => '54' ],
                    (object) [ 'heatpoint' => '98', 'value' => '50' ],
                    (object) [ 'heatpoint' => '99', 'value' => '45' ],
                    (object) [ 'heatpoint' => '100', 'value' => '3' ],
                    (object) [ 'heatpoint' => '101', 'value' => '29' ],
                    (object) [ 'heatpoint' => '102', 'value' => '29' ],
                    (object) [ 'heatpoint' => '103', 'value' => '15' ],
                    (object) [ 'heatpoint' => '104', 'value' => '95' ],
                    (object) [ 'heatpoint' => '105', 'value' => '161' ],
                    (object) [ 'heatpoint' => '106', 'value' => '348' ],
                    (object) [ 'heatpoint' => '107', 'value' => '692' ],
                    (object) [ 'heatpoint' => '108', 'value' => '707' ],
                    (object) [ 'heatpoint' => '109', 'value' => '876' ],
                    (object) [ 'heatpoint' => '110', 'value' => '462' ],
                    (object) [ 'heatpoint' => '111', 'value' => '450' ],
                    (object) [ 'heatpoint' => '112', 'value' => '421' ],
                    (object) [ 'heatpoint' => '113', 'value' => '418' ],
                    (object) [ 'heatpoint' => '114', 'value' => '214' ],
                    (object) [ 'heatpoint' => '115', 'value' => '210' ],
                    (object) [ 'heatpoint' => '116', 'value' => '211' ],
                    (object) [ 'heatpoint' => '117', 'value' => '190' ],
                    (object) [ 'heatpoint' => '118', 'value' => '225' ],
                    (object) [ 'heatpoint' => '119', 'value' => '129' ],
                    (object) [ 'heatpoint' => '120', 'value' => '102' ],
                    (object) [ 'heatpoint' => '121', 'value' => '32' ],
                    (object) [ 'heatpoint' => '122', 'value' => '202' ],
                    (object) [ 'heatpoint' => '123', 'value' => '121' ],
                    (object) [ 'heatpoint' => '124', 'value' => '46' ],
                    (object) [ 'heatpoint' => '125', 'value' => '10' ],
                    (object) [ 'heatpoint' => '126', 'value' => '24' ],
                    (object) [ 'heatpoint' => '127', 'value' => '21' ],
                    (object) [ 'heatpoint' => '128', 'value' => '4' ],
                    (object) [ 'heatpoint' => '129', 'value' => '33' ],
                    (object) [ 'heatpoint' => '130', 'value' => '152' ],
                    (object) [ 'heatpoint' => '131', 'value' => '257' ],
                    (object) [ 'heatpoint' => '132', 'value' => '522' ],
                    (object) [ 'heatpoint' => '133', 'value' => '526' ],
                    (object) [ 'heatpoint' => '134', 'value' => '552' ],
                    (object) [ 'heatpoint' => '135', 'value' => '555' ],
                    (object) [ 'heatpoint' => '136', 'value' => '286' ],
                    (object) [ 'heatpoint' => '137', 'value' => '400' ],
                    (object) [ 'heatpoint' => '138', 'value' => '393' ],
                    (object) [ 'heatpoint' => '139', 'value' => '428' ],
                    (object) [ 'heatpoint' => '140', 'value' => '171' ],
                    (object) [ 'heatpoint' => '141', 'value' => '123' ],
                    (object) [ 'heatpoint' => '142', 'value' => '77' ],
                    (object) [ 'heatpoint' => '143', 'value' => '298' ],
                    (object) [ 'heatpoint' => '144', 'value' => '179' ],
                    (object) [ 'heatpoint' => '145', 'value' => '81' ],
                    (object) [ 'heatpoint' => '146', 'value' => '154' ],
                    (object) [ 'heatpoint' => '147', 'value' => '51' ],
                    (object) [ 'heatpoint' => '148', 'value' => '97' ],
                    (object) [ 'heatpoint' => '149', 'value' => '20' ],
                    (object) [ 'heatpoint' => '151', 'value' => '29' ],
                    (object) [ 'heatpoint' => '152', 'value' => '17' ],
                    (object) [ 'heatpoint' => '153', 'value' => '16' ],
                    (object) [ 'heatpoint' => '154', 'value' => '58' ],
                    (object) [ 'heatpoint' => '155', 'value' => '454' ],
                    (object) [ 'heatpoint' => '156', 'value' => '625' ],
                    (object) [ 'heatpoint' => '157', 'value' => '693' ],
                    (object) [ 'heatpoint' => '158', 'value' => '692' ],
                    (object) [ 'heatpoint' => '159', 'value' => '655' ],
                    (object) [ 'heatpoint' => '160', 'value' => '1082' ],
                    (object) [ 'heatpoint' => '161', 'value' => '711' ],
                    (object) [ 'heatpoint' => '162', 'value' => '861' ],
                    (object) [ 'heatpoint' => '163', 'value' => '659' ],
                    (object) [ 'heatpoint' => '164', 'value' => '598' ],
                    (object) [ 'heatpoint' => '165', 'value' => '301' ],
                    (object) [ 'heatpoint' => '166', 'value' => '342' ],
                    (object) [ 'heatpoint' => '167', 'value' => '358' ],
                ]
            ],

            /*--************************************************ Quiz / Assignments */
            'quiz_assign' => [
                'quiz' => [
                    671 => (object) [
                        'result' => '0.8946360151667',
                        'firsttryresult' => '0.7971887550000',
                        'users' => '104',
                        'attempts' => '423',
                    ],
                    675 => (object) [
                        'result' => '0.8435',
                        'firsttryresult' => '0.7662',
                        'users' => '95',
                        'attempts' => '339',
                    ],
                    672 => (object) [
                        'result' => '0.8726599063750',
                        'firsttryresult' => '0.8358433733750',
                        'users' => '101',
                        'attempts' => '384',
                    ],
                    673 => (object) [
                        'result' => '0.8291909882000',
                        'firsttryresult' => '0.7401626016000',
                        'users' => '92',
                        'attempts' => '301',
                    ],
                    674 => (object) [
                        'result' => '0.7616637630000',
                        'firsttryresult' => '0.6389285714286',
                        'users' => '87',
                        'attempts' => '287',
                    ],
                    676 => (object) [
                        'result' => '0.8791909882000',
                        'firsttryresult' => '0.7901626016000',
                        'users' => '80',
                        'attempts' => '273',
                    ],
                ],
                'assign' => [
                    34 => (object) [
                        'handins' => '62',
                        'grade' => '0.765',
                    ],
                    38 => (object) [
                        'handins' => '60',
                        'grade' => '0.7344',
                    ],
                    35 => (object) [
                        'handins' => '57',
                        'grade' => '0.6855',
                    ],
                    36 => (object) [
                        'handins' => '55',
                        'grade' => '0.8224',
                    ],
                    37 => (object) [
                        'handins' => '48',
                        'grade' => '0.7882',
                    ],
                ]
            ],

            /*--************************************************ Quiz / Assignments */
            'activities' => [
                'data' => [
                    7765 => (object) [ 'modname' => 'resource', 'hits' => 89 ],
                    7790 => (object) [ 'modname' => 'resource', 'hits' => 44 ],
                    7791 => (object) [ 'modname' => 'resource', 'hits' => 252 ],
                    7766 => (object) [ 'modname' => 'assign', 'hits' => 126 ],
                    7767 => (object) [ 'modname' => 'quiz', 'hits' => 815 ],
                    7777 => (object) [ 'modname' => 'resource', 'hits' => 243 ],
                    7778 => (object) [ 'modname' => 'assign', 'hits' => 112 ],
                    7779 => (object) [ 'modname' => 'quiz', 'hits' => 792 ],
                    7768 => (object) [ 'modname' => 'resource', 'hits' => 223 ],
                    7769 => (object) [ 'modname' => 'assign', 'hits' => 118 ],
                    7770 => (object) [ 'modname' => 'quiz', 'hits' => 767 ],
                    7771 => (object) [ 'modname' => 'resource', 'hits' => 126 ],
                    7772 => (object) [ 'modname' => 'assign', 'hits' => 34 ],
                    7773 => (object) [ 'modname' => 'quiz', 'hits' => 526 ],
                    7774 => (object) [ 'modname' => 'resource', 'hits' => 96 ],
                    7775 => (object) [ 'modname' => 'assign', 'hits' => 46 ],
                    7776 => (object) [ 'modname' => 'quiz', 'hits' => 641 ],
                    7780 => (object) [ 'modname' => 'resource', 'hits' => 214 ],
                    7781 => (object) [ 'modname' => 'resource', 'hits' => 25 ],
                    7782 => (object) [ 'modname' => 'resource', 'hits' => 0 ],
                    7783 => (object) [ 'modname' => 'assign', 'hits' => 79 ],
                    7784 => (object) [ 'modname' => 'quiz', 'hits' => 601 ],
                    7785 => (object) [ 'modname' => 'url', 'hits' => 189 ],
                    7789 => (object) [ 'modname' => 'resource', 'hits' => 73 ],
                    7788 => (object) [ 'modname' => 'page', 'hits' => 67 ],
                    7787 => (object) [ 'modname' => 'wiki', 'hits' => 315 ],
                    7786 => (object) [ 'modname' => 'assign', 'hits' => 0 ]
                ]
            ]
        ];
    }

}
