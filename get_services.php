<?php
header('Content-Type: application/json; charset=utf-8');

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    echo json_encode(['success'=>false,'message'=>'DB connect error']);
    exit;
}

try{
    // ensure table exists (safe to run)
    $db->exec("CREATE TABLE IF NOT EXISTS services (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NOT NULL,
      price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      description TEXT,
      category VARCHAR(80) DEFAULT 'General',
      active TINYINT(1) DEFAULT 1,
      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $db->prepare('SELECT id, name, price, description, category FROM services WHERE active = 1 ORDER BY id DESC');
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalize categories and ensure Pediatric services appear as a single Consultation entry
    $out = [];
    $pediatric_candidates = [];

    foreach($rows as $r){
        $nameLower = mb_strtolower($r['name'] ?? '');
        if(strpos($nameLower, 'pedi') !== false || strpos($nameLower, 'pediatric') !== false){
            $pediatric_candidates[] = $r;
            continue; // collect and handle later as a single unified entry
        }

        // ensure common mis-categorized names go to sensible groups
        if(empty($r['category']) || strtolower($r['category']) === 'general'){
            if(strpos($nameLower, 'delivery') !== false || strpos($nameLower, 'birth') !== false){
                $r['category'] = 'Delivery';
            } elseif(strpos($nameLower, 'pregnan') !== false || strpos($nameLower, 'ob-') !== false || strpos($nameLower, 'obgyn') !== false || strpos($nameLower, 'obg') !== false){
                $r['category'] = 'Consultation';
            }
        }

        $out[] = $r;
    }

    // If any pediatric-related rows were found, produce a single unified Pediatric Consultation entry
    if(!empty($pediatric_candidates)){
        // prefer the first candidate as the authoritative price/description
        $p = $pediatric_candidates[0];
        $p['category'] = 'Consultation';
        $p['name'] = 'Pediatric Consultation';
        // enforce canonical pediatric price
        $p['price'] = 500;
        // ensure description includes follow-up pricing note if not present
        $followUpNote = 'Consultation services for infants and children — available via OB-GYN and midwife sessions. Follow-up visits ₱350.00.';
        if(empty(trim($p['description'] ?? ''))){
            $p['description'] = $followUpNote;
        } else {
            // append follow-up note if not already present
            if(stripos($p['description'], 'follow-up') === false){
                $p['description'] = trim($p['description']) . ' ' . $followUpNote;
            }
        }
        $out[] = $p;
    }

    echo json_encode(['success'=>true,'services'=>$out]);
    exit;
} catch(PDOException $e){
    echo json_encode(['success'=>false,'message'=>'Query failed: '.$e->getMessage()]);
    exit;
}
