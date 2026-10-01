<?php
require_once __DIR__ . '/Database.php';

class StatisticsService
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Top N pathogens by total cases in a date range.
     * If $pathogenId is given, only that pathogen is returned (still ranked).
     */
    public function topPathogens(string $start, string $end, int $limit = 3, ?int $pathogenId = null): array
    {
        $limit = (int)$limit;

        if ($pathogenId !== null) {
            $sql = "
                SELECT p.pathogen_id, p.pathogen_name, COUNT(*) AS total_cases
                FROM Infection_case ic
                JOIN Pathogen p ON ic.pathogen_id = p.pathogen_id
                WHERE ic.case_infection_date BETWEEN ? AND ?
                  AND ic.pathogen_id = ?
                GROUP BY p.pathogen_id, p.pathogen_name
                ORDER BY total_cases DESC
                LIMIT $limit
            ";
            return $this->db->query($sql, 'ssi', [$start, $end, $pathogenId]);
        }

        $sql = "
            SELECT p.pathogen_id, p.pathogen_name, COUNT(*) AS total_cases
            FROM Infection_case ic
            JOIN Pathogen p ON ic.pathogen_id = p.pathogen_id
            WHERE ic.case_infection_date BETWEEN ? AND ?
            GROUP BY p.pathogen_id, p.pathogen_name
            ORDER BY total_cases DESC
            LIMIT $limit
        ";
        return $this->db->query($sql, 'ss', [$start, $end]);
    }

    /**
     * Daily case counts for a set of pathogen IDs.
     * Returns ['labels' => [...], 'datasets' => [...]]
     */
    public function dailyCasesByPathogen(array $pathogenIds, string $start, string $end): array
    {
        if (empty($pathogenIds)) {
            return ['labels' => [], 'datasets' => []];
        }

        $placeholders = implode(',', array_fill(0, count($pathogenIds), '?'));
        $sql = "
            SELECT DATE(case_infection_date) AS day,
                   pathogen_id,
                   COUNT(*) AS cases
            FROM Infection_case
            WHERE pathogen_id IN ($placeholders)
              AND case_infection_date BETWEEN ? AND ?
            GROUP BY day, pathogen_id
            ORDER BY day ASC
        ";
        $types  = str_repeat('i', count($pathogenIds)) . 'ss';
        $params = array_merge($pathogenIds, [$start, $end]);
        $rows   = $this->db->query($sql, $types, $params);

        return $this->shapeTimeSeries($rows, $pathogenIds, $start, $end);
    }

    /**
     * Severity distribution (pie chart).
     * If $pathogenId is given, only that pathogen's cases are counted.
     * Returns ['labels' => ['Mild','Moderate','Severe'], 'data' => [12, 8, 3]]
     */
    public function severityDistribution(string $start, string $end, ?int $pathogenId = null): array
    {
        if ($pathogenId !== null) {
            $sql = "
                SELECT s.severity_name, COUNT(*) AS cases
                FROM Infection_case ic
                JOIN severity_level s ON ic.severity_id = s.severity_id
                WHERE ic.case_infection_date BETWEEN ? AND ?
                  AND ic.pathogen_id = ?
                GROUP BY s.severity_name
                ORDER BY s.severity_risk_score ASC
            ";
            $rows = $this->db->query($sql, 'ssi', [$start, $end, $pathogenId]);
        } else {
            $sql = "
                SELECT s.severity_name, COUNT(*) AS cases
                FROM Infection_case ic
                JOIN severity_level s ON ic.severity_id = s.severity_id
                WHERE ic.case_infection_date BETWEEN ? AND ?
                GROUP BY s.severity_name
                ORDER BY s.severity_risk_score ASC
            ";
            $rows = $this->db->query($sql, 'ss', [$start, $end]);
        }

        $labels = [];
        $data   = [];
        foreach ($rows as $r) {
            $labels[] = ucfirst($r['severity_name']);
            $data[]   = (int)$r['cases'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Weekly case counts (bar chart).
     * If $pathogenId is given, only that pathogen's cases are counted.
     * Returns ['labels' => ['W202636', ...], 'data' => [5, 8, 12]]
     */
    public function weeklyCases(string $start, string $end, ?int $pathogenId = null): array
    {
        if ($pathogenId !== null) {
            $sql = "
                SELECT YEARWEEK(case_infection_date, 1) AS week,
                       MIN(DATE(case_infection_date))   AS week_start,
                       COUNT(*)                          AS cases
                FROM Infection_case
                WHERE case_infection_date BETWEEN ? AND ?
                  AND pathogen_id = ?
                GROUP BY week
                ORDER BY week ASC
            ";
            $rows = $this->db->query($sql, 'ssi', [$start, $end, $pathogenId]);
        } else {
            $sql = "
                SELECT YEARWEEK(case_infection_date, 1) AS week,
                       MIN(DATE(case_infection_date))   AS week_start,
                       COUNT(*)                          AS cases
                FROM Infection_case
                WHERE case_infection_date BETWEEN ? AND ?
                GROUP BY week
                ORDER BY week ASC
            ";
            $rows = $this->db->query($sql, 'ss', [$start, $end]);
        }

        $labels = [];
        $data   = [];
        foreach ($rows as $r) {
            $labels[] = 'W' . $r['week'];
            $data[]   = (int)$r['cases'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // -------- private helpers --------

    private function shapeTimeSeries(array $rows, array $pathogenIds, string $start, string $end): array
    {
        $period = new DatePeriod(
            new DateTime($start),
            new DateInterval('P1D'),
            (new DateTime($end))->modify('+1 day')
        );
        $allDays = [];
        foreach ($period as $d) {
            $allDays[] = $d->format('Y-m-d');
        }

        $names = $this->pathogenNames($pathogenIds);

        $colors = ['#e74c3c', '#27ae60', '#2980b9', '#8e44ad', '#f39c12'];
        $datasets = [];
        foreach ($pathogenIds as $i => $pid) {
            $counts = array_fill_keys($allDays, 0);
            foreach ($rows as $r) {
                if ((int)$r['pathogen_id'] === (int)$pid) {
                    $counts[$r['day']] = (int)$r['cases'];
                }
            }
            $datasets[] = [
                'label'       => $names[$pid] ?? ('Pathogen ' . $pid),
                'data'        => array_values($counts),
                'borderColor' => $colors[$i % count($colors)],
            ];
        }

        return ['labels' => $allDays, 'datasets' => $datasets];
    }

    private function pathogenNames(array $ids): array
    {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql  = "SELECT pathogen_id, pathogen_name FROM Pathogen WHERE pathogen_id IN ($placeholders)";
        $types = str_repeat('i', count($ids));
        $rows = $this->db->query($sql, $types, $ids);

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['pathogen_id']] = $r['pathogen_name'];
        }
        return $map;
    }
}