<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS inventory (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        item_name VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        quantity INT DEFAULT 0,
        unit VARCHAR(64) DEFAULT NULL,
        reorder_level INT DEFAULT NULL,
        remarks TEXT DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure additional columns exist for older schemas where inventory table existed without them
    try{
        $needed = [
            'description' => 'TEXT DEFAULT NULL',
            'unit' => 'VARCHAR(64) DEFAULT NULL',
            'reorder_level' => 'INT DEFAULT NULL',
            'remarks' => 'TEXT DEFAULT NULL'
        ];
        $colCheck = $db->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inventory' AND COLUMN_NAME = :col LIMIT 1");
        foreach($needed as $col => $ddl){
            $colCheck->execute([':col' => $col]);
            if(!$colCheck->fetch()){
                $db->exec("ALTER TABLE inventory ADD COLUMN `$col` $ddl");
            }
        }
    } catch(PDOException $m){
        error_log('Inventory schema migration warning: ' . $m->getMessage());
    }

    $stmt = $db->query('SELECT id, item_name, description, quantity, unit, reorder_level, remarks, notes, created_by, created_at, updated_at FROM inventory ORDER BY item_name ASC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'inventory' => $rows]);
    exit;

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

?>
