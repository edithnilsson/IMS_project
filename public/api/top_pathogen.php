<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/StatisticsService.php';
require_once __DIR__ . '/../../src/JsonResponse.php';

$startDate  = $_GET['start_date']  ?? date('Y-m-d', strtotime('-29 days'));
$endDate    = $_GET['end_date']    ?? date('Y-m-d');
$pathogenId = isset($_GET['pathogen_id']) && $_GET['pathogen_id'] !== ''
              ? (int)$_GET['pathogen_id']
              : null;

$startDate  = $_GET['start_date']  ?? date('Y-m-d', strtotime('-29 days'));
$endDate    = $_GET['end_date']    ?? date('Y-m-d');
$pathogenId = isset($_GET['pathogen_id']) && $_GET['pathogen_id'] !== ''
              ? (int)$_GET['pathogen_id']
              : null;

try {
    $svc = new StatisticsService(new Database($conn));

    $top = $svc->topPathogens($startDate, $endDate, 3, $pathogenId);
    if (empty($top)) {
        JsonResponse::send(['labels' => [], 'datasets' => []]);
    }

    $ids    = array_column($top, 'pathogen_id');
    $series = $svc->dailyCasesByPathogen($ids, $startDate, $endDate);

    JsonResponse::send($series);
} catch (Throwable $e) {
    JsonResponse::error('Failed to fetch statistics', 500, ['detail' => $e->getMessage()]);
}

