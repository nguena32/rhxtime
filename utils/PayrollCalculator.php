<?php
// utils/PayrollCalculator.php

class PayrollCalculator {
    public static function getTheoreticalHours($pdo, $lieu_id, $month, $year) {
        $stmt = $pdo->prepare("SELECT * FROM planning_lieux WHERE lieu_id = ?");
        $stmt->execute([$lieu_id]);
        
        $planningByDay = [];
        while($row = $stmt->fetch()) {
            $planningByDay[$row['jour_semaine']] = $row;
        }

        $totalSeconds = 0;
        // Alternative à cal_days_in_month qui ne nécessite pas l'extension 'calendar'
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dayOfWeek = date('N', strtotime($date)); // 1 (Mon) to 7 (Sun)

            if (isset($planningByDay[$dayOfWeek]) && !$planningByDay[$dayOfWeek]['is_repos']) {
                $start = strtotime($planningByDay[$dayOfWeek]['heure_debut']);
                $end = strtotime($planningByDay[$dayOfWeek]['heure_fin']);
                $totalSeconds += ($end - $start);
            }
        }

        return $totalSeconds / 3600;
    }

    public static function getTheoreticalHoursFromData($planningByDay, $month, $year) {
        $totalSeconds = 0;
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dayOfWeek = date('N', strtotime($date));

            if (isset($planningByDay[$dayOfWeek]) && !$planningByDay[$dayOfWeek]['is_repos']) {
                $start = strtotime($planningByDay[$dayOfWeek]['heure_debut']);
                $end = strtotime($planningByDay[$dayOfWeek]['heure_fin']);
                $totalSeconds += ($end - $start);
            }
        }
        return $totalSeconds / 3600;
    }

    public static function calculateDelaySeconds($pointageTime, $scheduledStartTime) {
        $pt = strtotime(date('H:i:s', strtotime($pointageTime)));
        $st = strtotime($scheduledStartTime);
        if ($pt > $st) {
            return $pt - $st;
        }
        return 0;
    }
}
