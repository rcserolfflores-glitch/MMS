<?php
/**
 * Add missing columns to `medical_records` table so uploads can be attached.
 * Run: php add_medical_fields.php
 */
$host = 'localhost';
$dbname = 'drea_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
try{
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to DB $dbname\n";
    $cols = [
        'filename' => "VARCHAR(255) DEFAULT NULL",
        'file_url' => "VARCHAR(512) DEFAULT NULL",
        'note_type' => "VARCHAR(120) DEFAULT NULL"
    ];
    foreach($cols as $col => $def){
        $res = $db->query("SHOW COLUMNS FROM medical_records LIKE '" . $col . "'")->fetch(PDO::FETCH_ASSOC);
        if($res){ echo "Column $col already exists\n"; continue; }
        $sql = "ALTER TABLE medical_records ADD COLUMN $col $def";
        try{
            $db->exec($sql);
            echo "Added column $col\n";
        } catch (PDOException $e){ echo "Failed adding $col: " . $e->getMessage() . "\n"; }
    }
    echo "Done.\n";
} catch (PDOException $e){ echo "DB error: " . $e->getMessage() . "\n"; exit(1); }
?>