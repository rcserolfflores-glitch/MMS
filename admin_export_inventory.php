<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "Access denied";
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query('SELECT id, item_name, quantity, notes, created_by, created_at, updated_at FROM inventory ORDER BY item_name ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inventory_export_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','item_name','quantity','notes','created_by','created_at','updated_at']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'] ?? '',
            $r['item_name'] ?? '',
            $r['quantity'] ?? '',
            $r['notes'] ?? '',
            $r['created_by'] ?? '',
            $r['created_at'] ?? '',
            $r['updated_at'] ?? '',
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
