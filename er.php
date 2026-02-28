<?php
// Complete PHP file to generate an ER Diagram in Mermaid format for the 'dent_consent' database.
// This script connects to the database, queries the schema (tables, columns, PKs, FKs),
// and outputs an HTML page with Mermaid.js to render the ER diagram visually.
// Run this file in your browser (e.g., http://localhost/er_diagram.php).
// Assumptions:
// - Database: dent_consent (as per SQL dump).
// - XAMPP default: host=127.0.0.1, user=root, pass=''
// - If credentials differ, edit the PDO connection.
// - Requires internet for CDN (Mermaid.js), or download locally for offline.

// Hardcoded PDO connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=dent_consent', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to get all tables
function getTables($pdo) {
    $stmt = $pdo->query("SHOW TABLES");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Function to get columns for a table (with types and keys)
function getColumns($pdo, $table) {
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Simplify types for Mermaid (e.g., varchar -> string, int -> int, etc.)
    foreach ($columns as &$col) {
        $type = strtolower(preg_replace('/\(.*/', '', $col['Type'])); // Remove size, e.g., varchar(255) -> varchar
        if (in_array($type, ['varchar', 'char', 'text', 'longtext', 'mediumtext'])) {
            $col['simple_type'] = 'string';
        } elseif (in_array($type, ['int', 'bigint', 'smallint', 'tinyint'])) {
            $col['simple_type'] = 'int';
        } elseif (in_array($type, ['float', 'double', 'decimal'])) {
            $col['simple_type'] = 'float';
        } elseif ($type === 'timestamp' || $type === 'datetime' || $type === 'date') {
            $col['simple_type'] = 'date';
        } elseif ($type === 'enum') {
            $col['simple_type'] = 'enum';
        } else {
            $col['simple_type'] = $type; // Fallback
        }
        
        // Check if PK
        $col['is_pk'] = ($col['Key'] === 'PRI');
        
        // Check if auto-increment (for notation)
        $col['extra'] = $col['Extra'];
    }
    return $columns;
}

// Function to get foreign keys (relationships)
function getForeignKeys($pdo, $dbName) {
    $stmt = $pdo->prepare("
        SELECT 
            TABLE_NAME AS child_table,
            COLUMN_NAME AS child_column,
            REFERENCED_TABLE_NAME AS parent_table,
            REFERENCED_COLUMN_NAME AS parent_column
        FROM 
            information_schema.KEY_COLUMN_USAGE 
        WHERE 
            REFERENCED_TABLE_SCHEMA = :db 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute(['db' => $dbName]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Generate Mermaid ER Diagram syntax
function generateMermaidER($tables, $columnsMap, $foreignKeys) {
    $mermaid = "erDiagram\n";
    
    // Add entities (tables)
    foreach ($tables as $table) {
        $mermaid .= "    $table {\n";
        foreach ($columnsMap[$table] as $col) {
            $type = $col['simple_type'];
            $field = $col['Field'];
            $key = $col['is_pk'] ? ' PK' : '';
            // Note: Mermaid doesn't directly support AI, but we can add comment
            $comment = ($col['extra'] === 'auto_increment') ? ' "auto_increment"' : '';
            $mermaid .= "        $type $field$key$comment\n";
        }
        $mermaid .= "    }\n";
    }
    
    // Add relationships
    foreach ($foreignKeys as $fk) {
        $child = $fk['child_table'];
        $parent = $fk['parent_table'];
        // Assume one-to-many by default (||--o{ : "references")
        $mermaid .= "    $parent ||--o{ $child : \"references\"\n";
    }
    
    return $mermaid;
}

// Main logic
$dbName = 'dent_consent';
$tables = getTables($pdo);
$columnsMap = [];
foreach ($tables as $table) {
    $columnsMap[$table] = getColumns($pdo, $table);
}
$foreignKeys = getForeignKeys($pdo, $dbName);
$mermaidCode = generateMermaidER($tables, $columnsMap, $foreignKeys);

// Output HTML with Mermaid
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ER Diagram for dent_consent Database</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({ startOnLoad: true });
    </script>
    <style>
        body { font-family: Arial, sans-serif; }
        .mermaid { max-width: 100%; overflow: auto; }
    </style>
</head>
<body>
    <h1>Entity-Relationship Diagram for dent_consent</h1>
    <p>Generated using Mermaid.js. Scroll if needed.</p>
    <div class="mermaid">
<?php echo $mermaidCode; ?>
    </div>
</body>
</html>