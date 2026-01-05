<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

// export CSV of payments; optional filters: from, to (YYYY-MM-DD)
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $where = [];
    $params = [];
    if ($from !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = 'uploaded_at >= :from'; $params[':from'] = $from . ' 00:00:00'; }
    if ($to !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) { $where[] = 'uploaded_at <= :to'; $params[':to'] = $to . ' 23:59:59'; }

    $sql = 'SELECT * FROM payments' . (count($where) ? (' WHERE ' . implode(' AND ', $where)) : '') . ' ORDER BY uploaded_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="payments_export_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','patient_user_id','patient_name','filename','url','uploaded_at','verified','amount','reference_no','date_received','verified_by']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'] ?? '',
            $r['patient_user_id'] ?? '',
            $r['patient_name'] ?? '',
            $r['filename'] ?? '',
            $r['url'] ?? '',
            $r['uploaded_at'] ?? '',
            $r['verified'] ?? '',
            $r['amount'] ?? '',
            $r['reference_no'] ?? '',
            $r['date_received'] ?? '',
            $r['verified_by'] ?? '',
        ]);
    }
    fclose($out);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo 'DB error: ' . $e->getMessage();
    exit;
}

?>
