<?php
// Enhanced Database Manager for dent_consent
// Features: Full CRUD, search/filter, export CSV, row count, sortable columns, dark UI

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=dent_consent', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$action = $_GET['action'] ?? 'list_tables';
$table  = $_GET['table']  ?? null;
$id     = $_GET['id']     ?? null;

// Sanitize table/column names (whitelist approach)
function safeTable($pdo, $name) {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($name, $tables)) die("Invalid table.");
    return $name;
}

function getColumns($pdo, $table) {
    $stmt = $pdo->query("DESCRIBE `$table`");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listTables($pdo) {
    $stmt = $pdo->query("SHOW TABLE STATUS");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<div class="section-header"><h2>All Tables</h2></div>';
    echo '<div class="table-grid">';
    foreach ($tables as $t) {
        $name = $t['Name'];
        $rows = $t['Rows'] ?? '?';
        $size = $t['Data_length'] ? round($t['Data_length'] / 1024, 1) . ' KB' : '—';
        $engine = $t['Engine'] ?? '';
        echo "
        <a href='?action=view_table&table=$name' class='table-card'>
            <div class='table-card-icon'>⬡</div>
            <div class='table-card-name'>$name</div>
            <div class='table-card-meta'>
                <span>~$rows rows</span>
                <span>$size</span>
                <span class='engine-badge'>$engine</span>
            </div>
        </a>";
    }
    echo '</div>';
}

function viewTable($pdo, $table) {
    $columns = getColumns($pdo, $table);
    $pk = $columns[0]['Field'];

    // Search
    $search = $_GET['search'] ?? '';
    $sort   = $_GET['sort']   ?? $pk;
    $dir    = ($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
    $page   = max(1, intval($_GET['page'] ?? 1));
    $limit  = 25;
    $offset = ($page - 1) * $limit;

    // Validate sort column
    $validCols = array_column($columns, 'Field');
    if (!in_array($sort, $validCols)) $sort = $pk;

    // Export CSV
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$table.csv\"");
        $out = fopen('php://output', 'w');
        fputcsv($out, $validCols);
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) fputcsv($out, $row);
        fclose($out);
        exit;
    }

    // Count
    $where = '';
    $params = [];
    if ($search !== '') {
        $parts = [];
        foreach ($validCols as $col) {
            $parts[] = "`$col` LIKE ?";
            $params[] = "%$search%";
        }
        $where = 'WHERE ' . implode(' OR ', $parts);
    }
    $total = $pdo->prepare("SELECT COUNT(*) FROM `$table` $where");
    $total->execute($params);
    $totalRows = $total->fetchColumn();
    $totalPages = max(1, ceil($totalRows / $limit));

    $stmt = $pdo->prepare("SELECT * FROM `$table` $where ORDER BY `$sort` $dir LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $flipDir = $dir === 'ASC' ? 'DESC' : 'ASC';

    echo "<div class='section-header'>
        <h2><a href='?action=list_tables' class='breadcrumb'>Tables</a> / $table <span class='row-count'>$totalRows rows</span></h2>
        <div class='toolbar'>
            <form method='GET' class='search-form'>
                <input type='hidden' name='action' value='view_table'>
                <input type='hidden' name='table' value='$table'>
                <input type='text' name='search' value='".htmlspecialchars($search)."' placeholder='Search all columns…' class='search-input'>
                <button type='submit' class='btn btn-secondary'>Search</button>
                " . ($search ? "<a href='?action=view_table&table=$table' class='btn btn-ghost'>Clear</a>" : "") . "
            </form>
            <div class='toolbar-right'>
                <a href='?action=view_table&table=$table&export=1' class='btn btn-secondary'>↓ CSV</a>
                <a href='?action=insert&table=$table' class='btn btn-primary'>+ New Record</a>
            </div>
        </div>
    </div>";

    if (count($records) > 0) {
        echo "<div class='table-wrapper'><table class='data-table'><thead><tr>";
        foreach ($columns as $col) {
            $f = $col['Field'];
            $arrow = ($sort === $f) ? ($dir === 'ASC' ? ' ↑' : ' ↓') : '';
            echo "<th><a href='?action=view_table&table=$table&sort=$f&dir=$flipDir&search=".urlencode($search)."'>$f$arrow</a></th>";
        }
        echo "<th class='actions-col'>Actions</th></tr></thead><tbody>";

        foreach ($records as $row) {
            echo "<tr>";
            foreach ($columns as $col) {
                $f = $col['Field'];
                $v = $row[$f] ?? '';
                $display = strlen($v) > 80 ? htmlspecialchars(substr($v, 0, 80)) . '<span class="truncated">…</span>' : htmlspecialchars($v);
                if ($v === null || $v === '') $display = '<span class="null-val">—</span>';
                echo "<td>$display</td>";
            }
            $idValue = htmlspecialchars($row[$pk]);
            echo "<td class='actions-cell'>
                <a href='?action=edit&table=$table&id=$idValue' class='btn-sm btn-edit'>Edit</a>
                <a href='?action=delete&table=$table&id=$idValue' class='btn-sm btn-delete' onclick='return confirm(\"Delete this record?\");'>Del</a>
            </td></tr>";
        }
        echo "</tbody></table></div>";

        // Pagination
        echo "<div class='pagination'>";
        if ($page > 1) echo "<a href='?action=view_table&table=$table&page=".($page-1)."&search=".urlencode($search)."&sort=$sort&dir=$dir' class='btn btn-ghost'>← Prev</a>";
        echo "<span class='page-info'>Page $page of $totalPages</span>";
        if ($page < $totalPages) echo "<a href='?action=view_table&table=$table&page=".($page+1)."&search=".urlencode($search)."&sort=$sort&dir=$dir' class='btn btn-ghost'>Next →</a>";
        echo "</div>";
    } else {
        echo "<div class='empty-state'>No records found" . ($search ? " matching \"".htmlspecialchars($search)."\"" : "") . ".</div>";
    }
}

function recordForm($pdo, $table, $row = null) {
    $columns = getColumns($pdo, $table);
    $pk = $columns[0]['Field'];
    $isEdit = $row !== null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = []; $sets = [];
        foreach ($columns as $col) {
            $f = $col['Field'];
            if ($isEdit && $f === $pk) continue;
            if (!$isEdit && $col['Extra'] === 'auto_increment') continue;
            $data[$f] = $_POST[$f] !== '' ? $_POST[$f] : null;
            if ($isEdit) $sets[] = "`$f` = ?";
        }
        if ($isEdit) {
            $stmt = $pdo->prepare("UPDATE `$table` SET ".implode(', ', $sets)." WHERE `$pk` = ?");
            $params = array_values($data); $params[] = $row[$pk];
            $stmt->execute($params);
            echo "<div class='flash flash-success'>✓ Record updated successfully.</div>";
        } else {
            $fields = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $stmt = $pdo->prepare("INSERT INTO `$table` ($fields) VALUES ($placeholders)");
            $stmt->execute(array_values($data));
            echo "<div class='flash flash-success'>✓ Record inserted. ID: ".$pdo->lastInsertId()."</div>";
        }
        echo "<a href='?action=view_table&table=$table' class='btn btn-primary'>View Table</a>";
        return;
    }

    $title = $isEdit ? "Edit Record #" . htmlspecialchars($row[$pk]) : "New Record";
    echo "<div class='section-header'>
        <h2><a href='?action=list_tables' class='breadcrumb'>Tables</a> / <a href='?action=view_table&table=$table' class='breadcrumb'>$table</a> / $title</h2>
    </div>
    <form method='POST' class='record-form'>";

    foreach ($columns as $col) {
        $f = $col['Field'];
        $type = $col['Type'];
        if ($isEdit && $f === $pk) { echo "<input type='hidden' name='$f' value='".htmlspecialchars($row[$pk])."'>"; continue; }
        if (!$isEdit && $col['Extra'] === 'auto_increment') continue;
        $value = htmlspecialchars($row[$f] ?? '');
        $nullable = $col['Null'] === 'YES' ? '<span class="nullable">nullable</span>' : '<span class="required">*</span>';
        echo "<div class='form-group'>
            <label class='form-label'>$f <span class='type-hint'>$type</span> $nullable</label>";
        if (strpos($type, 'text') !== false) {
            echo "<textarea name='$f' class='form-control form-textarea'>$value</textarea>";
        } elseif (strpos($type, 'enum') !== false) {
            preg_match("/enum\((.*)\)/", $type, $m);
            $opts = explode(',', str_replace("'", "", $m[1]));
            echo "<select name='$f' class='form-control'>";
            foreach ($opts as $opt) {
                $sel = ($opt === ($row[$f] ?? '')) ? 'selected' : '';
                echo "<option value='$opt' $sel>$opt</option>";
            }
            echo "</select>";
        } elseif (strpos($type, 'tinyint(1)') !== false) {
            $chk = ($row[$f] ?? '') == 1 ? 'checked' : '';
            echo "<label class='toggle'><input type='hidden' name='$f' value='0'><input type='checkbox' name='$f' value='1' $chk class='toggle-input'><span class='toggle-slider'></span></label>";
        } elseif (strpos($type, 'date') !== false && strpos($type, 'time') === false) {
            echo "<input type='date' name='$f' value='$value' class='form-control'>";
        } elseif (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
            echo "<input type='datetime-local' name='$f' value='".str_replace(' ', 'T', $value)."' class='form-control'>";
        } elseif (strpos($type, 'int') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
            echo "<input type='number' name='$f' value='$value' class='form-control' step='any'>";
        } else {
            echo "<input type='text' name='$f' value='$value' class='form-control'>";
        }
        echo "</div>";
    }
    echo "<div class='form-actions'>
        <button type='submit' class='btn btn-primary'>".($isEdit ? '✓ Update' : '+ Insert')."</button>
        <a href='?action=view_table&table=$table' class='btn btn-ghost'>Cancel</a>
    </div></form>";
}

function deleteRecord($pdo, $table, $id) {
    $columns = getColumns($pdo, $table);
    $pk = $columns[0]['Field'];
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
    $stmt->execute([$id]);
    echo "<div class='flash flash-danger'>✓ Record deleted.</div>";
    echo "<a href='?action=view_table&table=$table' class='btn btn-primary'>View Table</a>";
}

// Get DB stats
$dbStmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
$tableCount = $dbStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DB Manager — dent_consent</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --bg:        #0d0f14;
    --surface:   #13161e;
    --surface2:  #1a1e29;
    --border:    #252a38;
    --border2:   #2e3347;
    --text:      #e2e8f0;
    --muted:     #64748b;
    --accent:    #3b82f6;
    --accent2:   #60a5fa;
    --green:     #22c55e;
    --red:       #ef4444;
    --amber:     #f59e0b;
    --mono:      'JetBrains Mono', monospace;
    --sans:      'Sora', sans-serif;
    --radius:    8px;
    --radius-lg: 14px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--sans);
    font-size: 14px;
    line-height: 1.6;
    min-height: 100vh;
}

/* Layout */
.app { display: flex; min-height: 100vh; }

.sidebar {
    width: 240px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    flex-shrink: 0;
}

.sidebar-logo {
    padding: 20px 20px 16px;
    border-bottom: 1px solid var(--border);
}

.sidebar-logo .logo-mark {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: var(--text);
}

.logo-icon {
    width: 32px; height: 32px;
    background: var(--accent);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    font-family: var(--mono);
    font-weight: 600;
    color: #fff;
}

.logo-text { font-weight: 600; font-size: 15px; }
.logo-sub { font-size: 11px; color: var(--muted); font-family: var(--mono); margin-top: 2px; }

.sidebar-section { padding: 16px 12px 8px; }
.sidebar-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--muted); padding: 0 8px 8px; font-weight: 600; }

.sidebar-tables { overflow-y: auto; flex: 1; }

.sidebar-table-link {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 12px;
    border-radius: var(--radius);
    color: var(--muted);
    text-decoration: none;
    font-size: 13px;
    font-family: var(--mono);
    transition: all .15s;
}
.sidebar-table-link:hover { background: var(--surface2); color: var(--text); }
.sidebar-table-link.active { background: rgba(59,130,246,.15); color: var(--accent2); }
.sidebar-table-link .tbl-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--border2); flex-shrink: 0; }
.sidebar-table-link.active .tbl-dot { background: var(--accent); }

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid var(--border);
    font-size: 11px;
    color: var(--muted);
    font-family: var(--mono);
}

/* Main content */
.main { flex: 1; min-width: 0; }

.topbar {
    height: 56px;
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 28px;
    gap: 16px;
    background: var(--surface);
    position: sticky; top: 0; z-index: 10;
}

.topbar-title { font-weight: 500; font-size: 15px; }
.topbar-spacer { flex: 1; }

.db-badge {
    background: rgba(59,130,246,.1);
    color: var(--accent2);
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-family: var(--mono);
    border: 1px solid rgba(59,130,246,.25);
}

.content { padding: 28px; }

/* Section header */
.section-header { margin-bottom: 20px; }
.section-header h2 { font-size: 20px; font-weight: 600; margin-bottom: 12px; }
.section-header .toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.toolbar-right { display: flex; gap: 8px; margin-left: auto; }

.breadcrumb { color: var(--muted); text-decoration: none; font-weight: 400; }
.breadcrumb:hover { color: var(--accent2); }
.breadcrumb::after { content: ' /'; color: var(--border2); margin: 0 4px; }

.row-count {
    font-size: 12px;
    color: var(--muted);
    background: var(--surface2);
    padding: 2px 8px;
    border-radius: 999px;
    font-family: var(--mono);
    vertical-align: middle;
    margin-left: 8px;
}

/* Buttons */
.btn {
    display: inline-flex; align-items: center;
    padding: 7px 14px;
    border-radius: var(--radius);
    font-size: 13px;
    font-family: var(--sans);
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all .15s;
    white-space: nowrap;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #2563eb; }
.btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border2); }
.btn-secondary:hover { border-color: var(--accent); color: var(--accent2); }
.btn-ghost { background: transparent; color: var(--muted); border: 1px solid var(--border); }
.btn-ghost:hover { color: var(--text); border-color: var(--border2); }

.btn-sm {
    display: inline-flex; align-items: center;
    padding: 3px 9px;
    border-radius: 5px;
    font-size: 12px;
    font-family: var(--mono);
    text-decoration: none;
    font-weight: 500;
    transition: all .15s;
}
.btn-edit { background: rgba(59,130,246,.12); color: var(--accent2); }
.btn-edit:hover { background: rgba(59,130,246,.25); }
.btn-delete { background: rgba(239,68,68,.1); color: #f87171; }
.btn-delete:hover { background: rgba(239,68,68,.22); }

/* Search */
.search-form { display: flex; align-items: center; gap: 8px; }
.search-input {
    background: var(--bg);
    border: 1px solid var(--border2);
    color: var(--text);
    padding: 7px 12px;
    border-radius: var(--radius);
    font-size: 13px;
    width: 240px;
    font-family: var(--sans);
    transition: border-color .15s;
}
.search-input:focus { outline: none; border-color: var(--accent); }
.search-input::placeholder { color: var(--muted); }

/* Table grid (list_tables) */
.table-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
}
.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    text-decoration: none;
    color: var(--text);
    transition: all .2s;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.table-card:hover { border-color: var(--accent); background: var(--surface2); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.4); }
.table-card-icon { font-size: 22px; color: var(--accent); }
.table-card-name { font-family: var(--mono); font-weight: 600; font-size: 14px; }
.table-card-meta { display: flex; gap: 8px; flex-wrap: wrap; font-size: 11px; color: var(--muted); font-family: var(--mono); }
.engine-badge { background: var(--surface2); padding: 1px 6px; border-radius: 4px; border: 1px solid var(--border2); }

/* Data table */
.table-wrapper { overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--border); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead tr { background: var(--surface); border-bottom: 1px solid var(--border); }
.data-table th { padding: 11px 14px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .8px; color: var(--muted); font-weight: 600; white-space: nowrap; }
.data-table th a { color: inherit; text-decoration: none; }
.data-table th a:hover { color: var(--accent2); }
.data-table tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
.data-table tbody tr:last-child { border-bottom: none; }
.data-table tbody tr:hover { background: var(--surface2); }
.data-table td { padding: 10px 14px; font-family: var(--mono); font-size: 12.5px; color: var(--text); max-width: 300px; overflow: hidden; }
.data-table .actions-col { width: 100px; }
.data-table .actions-cell { display: flex; gap: 6px; white-space: nowrap; }
.null-val { color: var(--muted); font-style: italic; }
.truncated { color: var(--muted); }

/* Pagination */
.pagination { display: flex; align-items: center; gap: 10px; margin-top: 16px; }
.page-info { color: var(--muted); font-size: 13px; }

/* Empty state */
.empty-state {
    text-align: center; padding: 60px 20px;
    color: var(--muted); font-size: 15px;
    border: 1px dashed var(--border); border-radius: var(--radius-lg);
}

/* Form */
.record-form { max-width: 700px; }
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 6px; font-family: var(--mono); }
.type-hint { color: var(--border2); font-weight: 400; margin-left: 6px; }
.nullable { color: var(--muted); font-size: 10px; font-weight: 400; margin-left: 6px; }
.required { color: var(--red); margin-left: 4px; }
.form-control {
    display: block; width: 100%;
    background: var(--surface);
    border: 1px solid var(--border2);
    color: var(--text);
    padding: 9px 13px;
    border-radius: var(--radius);
    font-size: 13px;
    font-family: var(--mono);
    transition: border-color .15s;
}
.form-control:focus { outline: none; border-color: var(--accent); }
.form-textarea { min-height: 110px; resize: vertical; }
select.form-control { cursor: pointer; }
.form-actions { display: flex; gap: 10px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--border); }

/* Toggle */
.toggle { display: inline-flex; align-items: center; cursor: pointer; gap: 10px; }
.toggle-input { display: none; }
.toggle-slider {
    width: 40px; height: 22px;
    background: var(--border2);
    border-radius: 999px;
    position: relative;
    transition: background .2s;
}
.toggle-slider::after {
    content: '';
    position: absolute;
    left: 3px; top: 3px;
    width: 16px; height: 16px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
}
.toggle-input:checked + .toggle-slider { background: var(--green); }
.toggle-input:checked + .toggle-slider::after { transform: translateX(18px); }

/* Flash messages */
.flash {
    padding: 12px 16px;
    border-radius: var(--radius);
    margin-bottom: 16px;
    font-size: 13px;
    font-weight: 500;
}
.flash-success { background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
.flash-danger { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.25); color: #f87171; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 99px; }
::-webkit-scrollbar-thumb:hover { background: var(--muted); }
</style>
</head>
<body>
<div class="app">

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="?action=list_tables" class="logo-mark">
            <div class="logo-icon">db</div>
            <div>
                <div class="logo-text">DB Manager</div>
                <div class="logo-sub">dent_consent</div>
            </div>
        </a>
    </div>

    <div class="sidebar-tables">
        <div class="sidebar-section">
            <div class="sidebar-section-title">Tables</div>
            <?php
            try {
                $sidebarStmt = $pdo->query("SHOW TABLES");
                $sidebarTables = $sidebarStmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($sidebarTables as $st) {
                    $active = ($table === $st) ? ' active' : '';
                    echo "<a href='?action=view_table&table=$st' class='sidebar-table-link$active'>
                        <span class='tbl-dot'></span>$st
                    </a>";
                }
            } catch (Exception $e) {}
            ?>
        </div>
    </div>

    <div class="sidebar-footer">
        <?= $tableCount ?> tables &nbsp;·&nbsp; MySQL
    </div>
</aside>

<!-- Main -->
<div class="main">
    <div class="topbar">
        <span class="topbar-title">
            <?php
            if ($action === 'list_tables') echo 'All Tables';
            elseif ($action === 'view_table') echo htmlspecialchars($table ?? '');
            elseif ($action === 'insert') echo 'New Record';
            elseif ($action === 'edit') echo 'Edit Record';
            elseif ($action === 'delete') echo 'Delete Record';
            ?>
        </span>
        <div class="topbar-spacer"></div>
        <span class="db-badge">dent_consent</span>
    </div>

    <div class="content">
        <?php
        try {
            if ($action === 'list_tables') {
                listTables($pdo);
            } elseif ($action === 'view_table' && $table) {
                $table = safeTable($pdo, $table);
                viewTable($pdo, $table);
            } elseif ($action === 'insert' && $table) {
                $table = safeTable($pdo, $table);
                recordForm($pdo, $table);
            } elseif ($action === 'edit' && $table && $id) {
                $table = safeTable($pdo, $table);
                $cols = getColumns($pdo, $table);
                $pk = $cols[0]['Field'];
                $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$row) die('<div class="flash flash-danger">Record not found.</div>');
                recordForm($pdo, $table, $row);
            } elseif ($action === 'delete' && $table && $id) {
                $table = safeTable($pdo, $table);
                deleteRecord($pdo, $table, $id);
            } else {
                listTables($pdo);
            }
        } catch (PDOException $e) {
            echo "<div class='flash flash-danger'>SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
</div>

</div>
</body>
</html>