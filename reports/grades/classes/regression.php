<?php

namespace lareport_grades;

class regression {

    public static function linear ($x, $y) {
        $sumX = array_sum($x);
        $sumY = array_sum($y);

        $sumXX = 0;
        $sumXY = 0;

        $n = count($x);
        for($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }

        // slope
        $slope = (($n * $sumXY) - ($sumX * $sumY)) / (($n * $sumXX) - ($sumX * $sumX));

        // intercept
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        return [
            'm' => $slope,
            'c' => $intercept
        ];
    }

}