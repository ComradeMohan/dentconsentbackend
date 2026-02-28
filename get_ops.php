<?php
require_once 'db_connect.php';

$stmt = $pdo->query("SELECT id, name, slug FROM operation_types");
$operation_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($operation_types, JSON_PRETTY_PRINT);
?>
