<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow GET for any logged-in user; require admin for mutating actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success'=>false,'message'=>'Not authenticated']);
        exit;
    }
} else {
    // POST/PUT/DELETE require admin
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
        http_response_code(403);
        echo json_encode(['success'=>false,'message'=>'Access denied']);
        exit;
    }
}

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS announcements (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) DEFAULT NULL,
        message TEXT DEFAULT NULL,
        published_at DATETIME DEFAULT NULL,
        audience VARCHAR(32) DEFAULT 'all',
        expires_at DATE DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_by INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ensure dismissals table exists (to persist user dismiss actions)
    $db->exec("CREATE TABLE IF NOT EXISTS announcement_dismissals (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        announcement_id INT UNSIGNED NOT NULL,
        user_id INT NOT NULL,
        dismissed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_ann_user (announcement_id, user_id),
        INDEX ix_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // ensure audit table exists (to record admin actions on announcements)
    $db->exec("CREATE TABLE IF NOT EXISTS announcement_audit (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        announcement_id INT UNSIGNED DEFAULT NULL,
        action VARCHAR(64) NOT NULL,
        performed_by INT NOT NULL,
        details TEXT DEFAULT NULL,
        performed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX ix_ann (announcement_id),
        INDEX ix_perf (performed_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // runtime-schema fix: ensure new columns exist on older installs
    try{
        $colsStmt = $db->query("SHOW COLUMNS FROM announcements");
        $cols = array_map(function($r){ return strtolower($r['Field'] ?? $r[0] ?? ''); }, $colsStmt->fetchAll(PDO::FETCH_ASSOC));
        if(!in_array('audience', $cols)){
            try{ $db->exec("ALTER TABLE announcements ADD COLUMN audience VARCHAR(32) DEFAULT 'all'"); } catch(Exception $e){}
        }
        if(!in_array('expires_at', $cols)){
            try{ $db->exec("ALTER TABLE announcements ADD COLUMN expires_at DATE DEFAULT NULL"); } catch(Exception $e){}
        }
    }catch(Exception $e){ /* ignore schema check errors */ }

    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        // list announcements
        // Public reads should only return announcements that are active and whose
        // published_at is in the past (or NULL = immediate). Admins can pass
        // `?all=1` to fetch everything for management.
        $isAdmin = (($_SESSION['user_type'] ?? '') === 'admin');
        $fetchAll = $isAdmin && (isset($_GET['all']) && $_GET['all'] == '1');

        // Execute selection with resilience: if the SELECT fails due to a missing
        // column on older installs, attempt to ALTER the table to add the
        // expected columns and retry once. If it still fails, return an empty
        // announcements list instead of leaving the UI stuck on Loading...
        $attempt = 0;
        $maxAttempts = 2;
        $rows = [];
        while ($attempt < $maxAttempts) {
            try {
                if ($fetchAll) {
                    $stmt = $db->prepare('SELECT id, title, message, published_at, audience, expires_at, is_active, created_by, created_at FROM announcements ORDER BY COALESCE(published_at, created_at) DESC');
                    $stmt->execute();
                } else {
                    // exclude announcements that the current user already dismissed
                    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                    if ($userId) {
                        $stmt = $db->prepare('SELECT a.id, a.title, a.message, a.published_at, a.audience, a.expires_at, a.is_active, a.created_by, a.created_at FROM announcements a WHERE a.is_active = 1 AND (a.published_at IS NULL OR a.published_at <= NOW()) AND a.id NOT IN (SELECT announcement_id FROM announcement_dismissals WHERE user_id = :uid) ORDER BY COALESCE(a.published_at, a.created_at) DESC');
                        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
                    } else {
                        $stmt = $db->prepare('SELECT id, title, message, published_at, audience, expires_at, is_active, created_by, created_at FROM announcements WHERE is_active = 1 AND (published_at IS NULL OR published_at <= NOW()) ORDER BY COALESCE(published_at, created_at) DESC');
                    }
                    $stmt->execute();
                }
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
            } catch (PDOException $e) {
                $attempt++;
                // if error indicates unknown column, try to add the expected columns
                $msg = strtolower($e->getMessage());
                if (strpos($msg, 'unknown column') !== false || strpos($msg, '42s22') !== false) {
                    try {
                        $db->exec("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS audience VARCHAR(32) DEFAULT 'all'");
                    } catch (Exception $ignore) {
                        try { $db->exec("ALTER TABLE announcements ADD COLUMN audience VARCHAR(32) DEFAULT 'all'"); } catch (Exception $i) {}
                    }
                    try {
                        $db->exec("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS expires_at DATE DEFAULT NULL");
                    } catch (Exception $ignore) {
                        try { $db->exec("ALTER TABLE announcements ADD COLUMN expires_at DATE DEFAULT NULL"); } catch (Exception $i) {}
                    }
                    // retry the select loop
                    continue;
                }
                // For any other DB error, log it and break so we can return a safe response
                $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $logFile = $logDir . DIRECTORY_SEPARATOR . 'admin_announcements_error.log';
                $errMsg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
                @file_put_contents($logFile, $errMsg, FILE_APPEND);
                break;
            }
        }

        // Return a safe response so the admin UI can render even if we couldn't
        // fetch announcements due to an older schema or other DB problems.
        echo json_encode(['success'=>true,'count'=>count($rows),'announcements'=>$rows]);
        exit;
    }

    // for POST, expect JSON body with action
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $action = strtolower(trim($data['action'] ?? ''));

    if ($action === 'create') {
        $title = trim($data['title'] ?? '');
        $message = trim($data['message'] ?? '');
        $published_at = trim($data['published_at'] ?? '') ?: null;
        $audience = trim($data['audience'] ?? 'all') ?: 'all';
        $expires = trim($data['expires'] ?? '') ?: null;
        $ins = $db->prepare('INSERT INTO announcements (title, message, published_at, audience, expires_at, created_by) VALUES (:title, :message, :published_at, :audience, :expires_at, :created_by)');
        $ins->bindValue(':title', $title ?: null);
        $ins->bindValue(':message', $message ?: null);
        $ins->bindValue(':published_at', $published_at ?: null);
        $ins->bindValue(':audience', $audience ?: 'all');
        $ins->bindValue(':expires_at', $expires ?: null);
        $ins->bindValue(':created_by', (int)$_SESSION['user_id'], PDO::PARAM_INT);
        $ins->execute();
        $id = (int)$db->lastInsertId();
        // record audit
        try{
            $audit = $db->prepare('INSERT INTO announcement_audit (announcement_id, action, performed_by, details) VALUES (:aid, :act, :uid, :det)');
            $details = json_encode(['title'=>$title, 'message'=>$message, 'published_at'=>$published_at, 'audience'=>$audience, 'expires_at'=>$expires]);
            $audit->bindValue(':aid', $id, PDO::PARAM_INT);
            $audit->bindValue(':act', 'create');
            $audit->bindValue(':uid', (int)$_SESSION['user_id'], PDO::PARAM_INT);
            $audit->bindValue(':det', $details);
            $audit->execute();
        }catch(Exception $e){ /* ignore audit failures */ }
        echo json_encode(['success'=>true,'id'=>$id]);
        exit;
    }

    if ($action === 'delete') {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
        $upd = $db->prepare('UPDATE announcements SET is_active = 0 WHERE id = :id');
        $upd->execute([':id'=>$id]);
        // record audit
        try{
            $audit = $db->prepare('INSERT INTO announcement_audit (announcement_id, action, performed_by) VALUES (:aid, :act, :uid)');
            $audit->bindValue(':aid', $id, PDO::PARAM_INT);
            $audit->bindValue(':act', 'delete');
            $audit->bindValue(':uid', (int)$_SESSION['user_id'], PDO::PARAM_INT);
            $audit->execute();
        }catch(Exception $e){ }
        echo json_encode(['success'=>true]);
        exit;
    }

    if ($action === 'disable' || $action === 'enable') {
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
        $val = ($action === 'enable') ? 1 : 0;
        $upd = $db->prepare('UPDATE announcements SET is_active = :val WHERE id = :id');
        $upd->execute([':val' => $val, ':id' => $id]);
        // record audit
        try{
            $audit = $db->prepare('INSERT INTO announcement_audit (announcement_id, action, performed_by, details) VALUES (:aid, :act, :uid, :det)');
            $audit->bindValue(':aid', $id, PDO::PARAM_INT);
            $audit->bindValue(':act', ($val===1 ? 'enable' : 'disable'));
            $audit->bindValue(':uid', (int)$_SESSION['user_id'], PDO::PARAM_INT);
            $audit->bindValue(':det', json_encode(['is_active'=>$val]));
            $audit->execute();
        }catch(Exception $e){ }
        echo json_encode(['success'=>true,'id'=>$id,'is_active'=>$val]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Unknown action']);
    exit;

} catch (PDOException $e) {
    // ensure logs directory
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'admin_announcements_error.log';
    $msg = '[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
    @file_put_contents($logFile, $msg, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
    exit;
}

?>
