<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/StatisticsService.php';
require_once __DIR__ . '/../src/JsonResponse.php';

$startDate  = $_GET['start_date']  ?? date('Y-m-d', strtotime('-83 days'));
$endDate    = $_GET['end_date']    ?? date('Y-m-d');
$pathogenId = isset($_GET['pathogen_id']) && $_GET['pathogen_id'] !== ''
              ? (int)$_GET['pathogen_id']
              : null;

try {
    $svc = new StatisticsService(new Database($conn));
    JsonResponse::send($svc->weeklyCases($startDate, $endDate, $pathogenId));
} catch (Throwable $e) {
    JsonResponse::error('Failed to fetch weekly cases', 500, ['detail' => $e->getMessage()]);
}