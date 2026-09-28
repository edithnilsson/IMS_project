<?php
header('Content-Type: application/json');
ini_set('serialize_precision', '-1');
require_once __DIR__ . '/../config/db.php';

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-29 days'));
$endDate   = $_GET['end_date']   ?? date('Y-m-d');

try {
    if ($conn->connect_error) {
        throw new Exception('DB connection failed');
    }

    // This SQL query selects the top 3 pathogens between 2 dates still to be specified
    $sql = "
        SELECT p.pathogen_id, p.pathogen_name, COUNT(*) AS total_cases
        FROM Infection_case ic
        JOIN Pathogen p ON ic.pathogen_id = p.pathogen_id
        WHERE ic.case_infection_date BETWEEN ? AND ?
        GROUP BY p.pathogen_id, p.pathogen_name
        ORDER BY total_cases DESC
        LIMIT 3
    ";
    $stmt = $conn->prepare($sql); 
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $topPathogens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($topPathogens)) {
        echo json_encode(['labels' => [], 'datasets' => []]);
        exit;
    }

    $pathogenIds = array_column($topPathogens, 'pathogen_id');

    // Daily counts, this SQL query is in charge of counting how many infection cases happen per day
    $placeholders = implode(',', array_fill(0, count($pathogenIds), '?'));
    $sql = "
        SELECT DATE(ic.case_infection_date) AS day,
               ic.pathogen_id,
               COUNT(*) AS cases
        FROM Infection_case ic
        WHERE ic.pathogen_id IN ($placeholders)
          AND ic.case_infection_date BETWEEN ? AND ?
        GROUP BY day, ic.pathogen_id
        ORDER BY day ASC
    ";
    $stmt = $conn->prepare($sql);

    //  Build and execute the query. SQL injection taken in mind with prepare()
    $types  = str_repeat('i', count($pathogenIds)) . 'ss'; # Creates a string of "iss" characters for pathogenID and dates
    $params = array_merge($pathogenIds, [$startDate, $endDate]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Determine the list of days
    $period  = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );
    $allDays = [];
    foreach ($period as $d) {
        $allDays[] = $d->format('Y-m-d');
    }

    // Get 1 dataset per pathogen
    $colors   = ['#e74c3c', '#27ae60', '#2980b9']; # Colors.
    $datasets = [];
    foreach ($topPathogens as $i => $p) {
        $counts = array_fill_keys($allDays, 0);
        foreach ($rows as $r) {
            if ((int)$r['pathogen_id'] === (int)$p['pathogen_id']) {
                $counts[$r['day']] = (int)$r['cases'];
            }
        }
        $datasets[] = [
            'label'       => $p['pathogen_name'],
            'data'        => array_values($counts),
            'borderColor' => $colors[$i % count($colors)],
            'pointRadius' => 2,
            'tension'     => 0.4,
            'fill'        => false,
        ];
    }

    echo json_encode([
        'labels'   => $allDays,
        'datasets' => $datasets,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch statistics', 'detail' => $e->getMessage()]);
}