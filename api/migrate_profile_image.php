<?php
require_once 'db_connect.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER role");
    echo json_encode(["success" => true, "message" => "Column profile_image added successfully"]);
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo json_encode(["success" => true, "message" => "Column profile_image already exists"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>
