<?php
include "../db.php";

function formatValue($value) {
    if ($value === null || $value === '') {
        return '';
    }

    if (is_numeric($value)) {
        return number_format((float)$value, 2, '.', ',');
    }

    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatAveValue($value) {
    if ($value === null || trim((string)$value) === '') {
        return '';
    }

    $value = str_replace(',', '', trim((string)$value));

    if (!is_numeric($value)) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    $number = (float)$value;

    if ($number == floor($number)) {
        return number_format($number, 0, '.', ',');
    }

    return rtrim(rtrim(number_format($number, 10, '.', ','), '0'), '.');
}

$month = $_GET['month'] ?? '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($month === '') {
    die("Invalid month.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM admin WHERE month = ? AND year = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "si", $month, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$record = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$record) {
    die("Record not found.");
}

$record_id = (int)$record['id'];
$tombo_no = $record['tombo_no'] ?? '';
$working_days = $record['working_days'] ?? 0;
$ave_1 = $record['ave_1'] ?? '';
$ave_2 = $record['ave_2'] ?? '';

$opening_balance = json_decode($record['opening_balance'] ?? '{}', true);
$date_data = json_decode($record['date_data'] ?? '{}', true);

if (!is_array($opening_balance)) $opening_balance = [];
if (!is_array($date_data)) $date_data = [];

$items = [
    'from_production_theoretical' => 'From Production-Theoretical',
    'from_production_actual' => 'From Production-Actual',
    'balance_waste_before_crush' => 'Balance Waste Before Crush',
    'crushing_process_theoretical' => 'Crushing Process-Theoretical',
    'crushing_process_actual' => 'Crushing Process-Actual',
    'balance_waste_after_crush' => 'Balance Waste After Crush',
    'in_theoretical' => 'In-Theoretical',
    'theoretical_powder' => 'Theoretical Powder',
    'in_actual' => 'In-Actual',
    'actual_powder' => 'Actual Powder',
    'out_qty' => 'Out',
    'phoenix' => 'Phoenix',
    'daiko' => 'DAIKO (D 6000)',
    'balance_powder' => 'Balance Powder'
];

function getOpeningValue($opening_balance, $key) {
    return isset($opening_balance[$key]) && $opening_balance[$key] !== ''
        ? $opening_balance[$key]
        : '';
}

function getDateValue($date_data, $date, $key) {
    return isset($date_data[$date]) &&
        is_array($date_data[$date]) &&
        array_key_exists($key, $date_data[$date])
        ? $date_data[$date][$key]
        : '';
}

$dates = array_keys($date_data);

usort($dates, function($a, $b) {
    $timeA = strtotime($a);
    $timeB = strtotime($b);

    if ($timeA === false || $timeB === false) {
        return strcmp($a, $b);
    }

    return $timeA <=> $timeB;
});

function getClosingValue($date_data, $dates, $key) {
    $sumItems = [
        'from_production_theoretical',
        'from_production_actual',
        'crushing_process_theoretical',
        'crushing_process_actual',
        'in_theoretical',
        'in_actual'
    ];

    $lastItems = [
        'balance_waste_before_crush',
        'balance_waste_after_crush',
        'theoretical_powder',
        'actual_powder',
        'balance_powder'
    ];

    $values = [];

    foreach ($dates as $date) {
        $value = getDateValue($date_data, $date, $key);

        if ($value !== '' && $value !== null && is_numeric($value)) {
            $values[] = (float)$value;
        }
    }

    if (in_array($key, $sumItems, true)) {
        return array_sum($values);
    }

    if (in_array($key, $lastItems, true)) {
        return count($values) > 0 ? end($values) : '';
    }

    return array_sum($values);
}

$phoenixTotal = 0;
$daikoTotal = 0;

foreach ($dates as $date) {
    $phoenixValue = getDateValue($date_data, $date, 'phoenix');
    $daikoValue = getDateValue($date_data, $date, 'daiko');

    if ($phoenixValue !== '' && is_numeric($phoenixValue)) {
        $phoenixTotal += (float)$phoenixValue;
    }

    if ($daikoValue !== '' && is_numeric($daikoValue)) {
        $daikoTotal += (float)$daikoValue;
    }
}

$phoenixDaikoClosing = $phoenixTotal + $daikoTotal;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin View - Waste Recycle</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,Helvetica,sans-serif;background:#f4f7fb;color:#333}
.sidebar{position:fixed;left:0;top:0;width:235px;height:100vh;background:#1e1768;color:white;padding-top:25px;z-index:1000}
.logo-box{text-align:center;padding-bottom:25px}
.logo-box img{width:65px;height:65px;object-fit:contain;background:white;padding:5px}
.system-title{font-size:17px;font-weight:bold;margin-top:10px}
.system-subtitle{font-size:12px;margin-top:4px;opacity:.8}
.sidebar-line{height:1px;background:rgba(255,255,255,.25);margin:0 18px 18px}
.menu{padding:0 12px}
.menu a,.logout a{display:flex;align-items:center;gap:13px;color:white;text-decoration:none;padding:13px 15px;margin-bottom:5px;border-radius:7px;font-size:14px}
.menu a:hover,.menu a.active,.logout a:hover{background:#2f4da0}
.menu-icon{width:22px;text-align:center;font-size:18px}
.logout{position:absolute;bottom:20px;left:12px;right:12px}
.main{margin-left:235px;padding:30px}
.page-header{margin-bottom:25px}
.page-header h1{color:#243b7a;font-size:28px}
.page-header p{margin-top:5px;color:#777;font-size:14px}
.form-card{background:white;padding:25px;border-radius:12px;box-shadow:0 3px 12px rgba(0,0,0,.07)}
.information-section{border:1px solid #e0e4eb;border-radius:10px;padding:20px;background:#fafbfc;margin-bottom:25px}
.information-title{font-size:14px;font-weight:bold;color:#243b7a;margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid #e1e5ec}
.information-grid{display:grid;grid-template-columns:1.8fr .8fr 1fr 1fr;gap:15px}
.form-group{background:white;border:1px solid #dfe3ea;border-radius:8px;padding:14px;min-height:105px}
.form-group label{display:block;font-size:12px;font-weight:bold;color:#4b5563;margin-bottom:8px}
.value-box{width:100%;min-height:38px;border:1px solid #d2d7df;border-radius:5px;padding:9px 10px;font-size:13px;background:white;display:flex;align-items:center;overflow-wrap:anywhere}
.ave-box{width:100%;height:76px;border:1px solid #d6d9e2;border-radius:6px;background:white;overflow:hidden;display:flex;flex-direction:column}
.ave-value{height:37px;display:flex;align-items:center;justify-content:center;font-size:13px;position:relative;padding-right:20px;overflow-wrap:anywhere}
.ave-line{width:100%;height:1px;background:#d6d9e2}
.percent-symbol{position:absolute;right:12px;font-size:13px;color:#444}
.working-days-value{max-width:150px}
.table-wrapper{width:100%;overflow-x:auto;border:1px solid #dfe3ea;background:white}
.record-table{border-collapse:collapse;min-width:1100px;width:100%}
.record-table th,.record-table td{border:1px solid #e0e3e8;padding:8px;text-align:center}
.record-table th{background:#243b7a;color:white;font-size:12px;font-weight:bold;height:45px}
.record-table td{background:white;font-size:12px;height:42px}
.record-table tbody tr:nth-child(even) td{background:#fafbfc}
.items-header,.item-name{width:280px;min-width:280px}
.opening-header,.closing-header{width:160px;min-width:160px}
.item-name{text-align:left!important;font-weight:600;background:#f4f7fb!important;position:sticky;left:0;z-index:2}
.date-header,.date-cell{min-width:160px}
.data-value{display:block;width:100%;min-height:32px;padding:8px 7px;text-align:center;font-size:12px;overflow-wrap:anywhere}
.auto-field{background:#f1f4f9!important;color:#243b7a!important;font-weight:bold}
.record-table tr[data-item="out_qty"] td,.record-table tr[data-item="balance_powder"] td{border-top:2px solid #243b7a!important;border-bottom:2px solid #243b7a!important}
.record-table tr[data-item="out_qty"] .item-name,.record-table tr[data-item="balance_powder"] .item-name{font-weight:bold;color:#243b7a}
.merged-closing{vertical-align:middle!important;background:#f1f4f9!important;color:#243b7a!important;font-weight:bold}
.button-area{margin-top:22px;display:flex;justify-content:flex-start;align-items:center;flex-wrap:wrap;gap:10px}
.back-btn,.edit-btn,.export-btn{color:white;border:none;padding:11px 22px;border-radius:6px;text-decoration:none;cursor:pointer;font-weight:bold;font-size:13px;display:inline-flex;align-items:center;justify-content:center;gap:7px}
.back-btn{background:#243b7a}.back-btn:hover{background:#2f4da0}
.edit-btn{background:#f0ad4e;color:#212529}.edit-btn:hover{background:#e09b32}
.export-btn{background:#198754}.export-btn:hover{background:#157347}
@media(max-width:1000px){.information-grid{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.sidebar{width:200px}.main{margin-left:200px;padding:20px}.information-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo-box">
        <img src="../img/LogoNichias.jpg" alt="NICHIAS Logo">
        <div class="system-title">ADMIN SYSTEM</div>
        <div class="system-subtitle">Waste Recycle</div>
    </div>
    <div class="sidebar-line"></div>
    <div class="menu">
        <a href="dashboard.php"><span class="menu-icon"><i class="fa-solid fa-chart-pie"></i></span>Dashboard</a>
        <a href="admin_form.php"><span class="menu-icon"><i class="fa-solid fa-file-pen"></i></span>Admin Form</a>
        <a href="admin_record.php" class="active"><span class="menu-icon"><i class="fa-solid fa-folder-open"></i></span>Admin Records</a>
        <a href="operator_record.php"><span class="menu-icon"><i class="fa-solid fa-users-gear"></i></span>Operator Records</a>
    </div>
    <div class="logout"><a href="../logout.php"><span class="menu-icon"><i class="fa-solid fa-right-from-bracket"></i></span>Logout</a></div>
</div>

<div class="main">
    <div class="page-header">
        <h1>Admin View</h1>
        <p>View Waste Recycle record for <?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?> <?= $year ?></p>
    </div>

    <div class="form-card">
        <div class="information-section">
            <div class="information-title">Production Information</div>
            <div class="information-grid">
                <div class="form-group">
                    <label>Tombo No. 1995 Production Estimation Qty (Kgs)</label>
                    <div class="value-box"><?= htmlspecialchars((string)$tombo_no, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="form-group">
                    <label>Ave</label>
                    <div class="ave-box">
                        <div class="ave-value">
                            <?= formatAveValue($ave_1) ?>
                            <span class="percent-symbol">%</span>
                        </div>
                        <div class="ave-line"></div>
                        <div class="ave-value"><?= formatAveValue($ave_2) ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Month</label>
                    <div class="value-box"><?= htmlspecialchars($month, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="form-group">
                    <label>Working Days</label>
                    <div class="value-box working-days-value"><?= number_format((float)$working_days, 0) ?></div>
                </div>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="record-table">
                <thead>
                    <tr>
                        <th class="items-header">Items</th>
                        <th class="opening-header">Opening Balance</th>
                        <?php foreach ($dates as $date): ?>
                            <th class="date-header"><?= htmlspecialchars((string)$date, ENT_QUOTES, 'UTF-8') ?></th>
                        <?php endforeach; ?>
                        <th class="closing-header">Closing Balance</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $key => $label): ?>
                    <tr data-item="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
                        <td class="item-name"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="data-value auto-field">
                            <?php
                            if ($key !== 'out_qty') {
                                echo formatValue(getOpeningValue($opening_balance, $key));
                            }
                            ?>
                        </span></td>

                        <?php foreach ($dates as $date): ?>
                            <td class="date-cell">
                                <span class="data-value <?php
                                    if (in_array($key, [
                                        'balance_waste_before_crush',
                                        'balance_waste_after_crush',
                                        'theoretical_powder',
                                        'actual_powder',
                                        'out_qty',
                                        'balance_powder'
                                    ], true)) echo 'auto-field';
                                ?>">
                                <?php
                                $value = getDateValue($date_data, $date, $key);

                                if ($key === 'out_qty') {
                                    $phoenix = getDateValue($date_data, $date, 'phoenix');
                                    $daiko = getDateValue($date_data, $date, 'daiko');

                                    $hasPhoenix = $phoenix !== '' && $phoenix !== null && is_numeric($phoenix);
                                    $hasDaiko = $daiko !== '' && $daiko !== null && is_numeric($daiko);

                                    if ($hasPhoenix || $hasDaiko) {
                                        echo formatValue(
                                            ($hasPhoenix ? (float)$phoenix : 0) +
                                            ($hasDaiko ? (float)$daiko : 0)
                                        );
                                    }
                                } else {
                                    echo formatValue($value);
                                }
                                ?>
                                </span>
                            </td>
                        <?php endforeach; ?>

                        <?php if ($key === 'phoenix'): ?>
                            <td class="merged-closing" rowspan="2">
                                <span class="data-value auto-field"><?= formatValue($phoenixDaikoClosing) ?></span>
                            </td>
                        <?php elseif ($key === 'daiko'): ?>
                            <!-- Closing cell merged with Phoenix -->
                        <?php elseif ($key === 'out_qty'): ?>
                            <td><span class="data-value auto-field">TOTAL</span></td>
                        <?php else: ?>
                            <td><span class="data-value auto-field">
                                <?= formatValue(getClosingValue($date_data, $dates, $key)) ?>
                            </span></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="button-area">
            <a href="admin_record.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <a href="admin_form.php?id=<?= $record_id ?>&month=<?= urlencode($month) ?>&year=<?= $year ?>" class="edit-btn">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <a href="export_excel.php?id=<?= $record_id ?>" class="export-btn">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>
</div>
</body>
</html>