<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/StatisticsService.php';
require_once __DIR__ . '/../src/JsonResponse.php';
require_once __DIR__ . "/src/authenticate_check.php";



// Access control
if (!isset($_SESSION['privUser']) 
    || !($_SESSION['privUser'] instanceof PrivilegedUser)
    || !$_SESSION['privUser']->hasPrivilege('view_cross_hospital')) {

    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// ---------- Input ----------
$start      = $_GET['start_date']  ?? date('Y-m-d', strtotime('-29 days'));
$end        = $_GET['end_date']    ?? date('Y-m-d');
$hospitalId = isset($_GET['hospital_id']) && $_GET['hospital_id'] !== ''
              ? $_GET['hospital_id'] : null;
$pathogenId = isset($_GET['pathogen_id']) && $_GET['pathogen_id'] !== ''
              ? (int)$_GET['pathogen_id'] : null;

// ---------- Query and respond ----------
try {
    $svc = new StatisticsService(new Database($conn));

    $cases  = $svc->crossHospitalCases($start, $end, $hospitalId, $pathogenId);
    $chiefs = $svc->chiefEpidemiologists();

    $rows = [];
    foreach ($cases as $c) {
        $hid   = $c['hospital_id'];
        $chief = $chiefs[$hid] ?? null;

        $rows[] = [
            'hospital_name' => $c['hospital_name'],
            'pathogen_name' => $c['pathogen_name'],
            'cases'         => (int)$c['cases'],
            'chief_name'    => $chief
                ? trim($chief['user_name'] . ' ' . $chief['user_surname'])
                : '—',
            'chief_email'   => $chief['user_email']        ?? '—',
            'chief_phone'   => $chief['user_phone_number'] ?? '—',
        ];
    }

    JsonResponse::send(['rows' => $rows]);

} catch (Throwable $e) {
    JsonResponse::error('Failed to fetch cross-hospital cases', 500, [
        'detail' => $e->getMessage(),
    ]);
}