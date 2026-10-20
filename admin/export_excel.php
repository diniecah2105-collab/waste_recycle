<?php

include "../db.php";

/*
=========================================================
1. AMBIL RECORD ID
=========================================================
*/

$record_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($record_id <= 0) {
    die("Invalid record ID.");
}


/*
=========================================================
2. SENARAI ITEM
=========================================================
*/

$items = [
    "from_production_theoretical" => "From Production-Theoretical",
    "from_production_actual" => "From Production-Actual",
    "balance_waste_before_crush" => "Balance Waste Before Crush",
    "crushing_process_theoretical" => "Crushing Process-Theoretical",
    "crushing_process_actual" => "Crushing Process-Actual",
    "balance_waste_after_crush" => "Balance Waste After Crush",
    "in_theoretical" => "In-Theoretical",
    "theoretical_powder" => "Theoretical Powder",
    "in_actual" => "In-Actual",
    "actual_powder" => "Actual Powder",
    "out_qty" => "Out",
    "phoenix" => "Phoenix",
    "daiko" => "DAIKO (D 6000)",
    "balance_powder" => "Balance Powder"
];


/*
=========================================================
3. AMBIL RECORD ADMIN
=========================================================
*/

$sql = "SELECT * FROM admin WHERE id = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database Prepare Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $record_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    die("Record tidak dijumpai.");
}

$row = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
=========================================================
4. MAKLUMAT UTAMA
=========================================================
*/

$month = $row['month'] ?? '';
$year = $row['year'] ?? date('Y');
$tombo_no = $row['tombo_no'] ?? '';
$ave_1 = $row['ave_1'] ?? 0;
$ave_2 = $row['ave_2'] ?? 0;
$working_days = $row['working_days'] ?? 0;


/*
=========================================================
5. NAMA BULAN
=========================================================
*/

$month_names = [
    1 => "January",
    2 => "February",
    3 => "March",
    4 => "April",
    5 => "May",
    6 => "June",
    7 => "July",
    8 => "August",
    9 => "September",
    10 => "October",
    11 => "November",
    12 => "December"
];

$month_name = $month_names[(int)$month] ?? $month;


/*
=========================================================
6. OPENING BALANCE
=========================================================
*/

$opening_balance = json_decode(
    $row['opening_balance'] ?? '{}',
    true
);

if (!is_array($opening_balance)) {
    $opening_balance = [];
}


/*
=========================================================
7. DATE DATA
=========================================================
*/

$date_data = json_decode(
    $row['date_data'] ?? '{}',
    true
);

if (!is_array($date_data)) {
    $date_data = [];
}


/*
=========================================================
8. SUSUN TARIKH
=========================================================
*/

uksort($date_data, function ($a, $b) {
    return strcmp($a, $b);
});


/*
=========================================================
9. FORMAT NOMBOR
=========================================================
*/

function formatNumber($value)
{
    if ($value === null || $value === '') {
        return '0.00';
    }

    if (!is_numeric($value)) {
        return htmlspecialchars(
            (string)$value,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    return number_format((float)$value, 2, '.', '');
}


/*
=========================================================
10. DAPATKAN NILAI HARIAN
=========================================================
*/

function getDateValue($date_data, $date, $key)
{
    if (
        isset($date_data[$date]) &&
        is_array($date_data[$date]) &&
        array_key_exists($key, $date_data[$date])
    ) {
        return $date_data[$date][$key];
    }

    return '';
}


/*
=========================================================
11. JUMLAH PHOENIX + DAIKO
=========================================================
*/

$phoenixTotal = 0;
$daikoTotal = 0;

foreach ($date_data as $date => $values) {
    $phoenixValue = getDateValue(
        $date_data,
        $date,
        'phoenix'
    );

    $daikoValue = getDateValue(
        $date_data,
        $date,
        'daiko'
    );

    if (is_numeric($phoenixValue)) {
        $phoenixTotal += (float)$phoenixValue;
    }

    if (is_numeric($daikoValue)) {
        $daikoTotal += (float)$daikoValue;
    }
}

$phoenixDaikoClosing = $phoenixTotal + $daikoTotal;


/*
=========================================================
12. NAMA FAIL
=========================================================
*/

$filename = "Admin_Record_"
    . preg_replace('/[^a-zA-Z0-9_-]/', '_', $month_name)
    . "_"
    . $year
    . "_ID_"
    . $record_id
    . ".xls";


/*
=========================================================
13. HEADER EXCEL
=========================================================
*/

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>

/* ========================================================
   ASAS
======================================================== */

body {
    font-family: Calibri, Arial, sans-serif;
    font-size: 11pt;
    color: #1f2937;
    background: #ffffff;
    margin: 10px;
}

/*
   Border HTML turut digunakan kerana sesetengah versi Excel
   tidak memaparkan border CSS bagi cell kosong dengan konsisten.
*/

table {
    border-collapse: collapse;
    mso-cellspacing: 0;
    empty-cells: show;
}

/* ========================================================
   TAJUK
======================================================== */

.title {
    font-size: 20px;
    font-weight: bold;
    color: #ffffff;
    text-align: center;
    vertical-align: middle;
    background-color: #163a5f;
    padding: 12px 10px;
    border: 1px solid #000000;
    mso-border-alt: solid #000000 0.5pt;
}

.subtitle {
    font-size: 14px;
    font-weight: bold;
    color: #163a5f;
    text-align: center;
    vertical-align: middle;
    background-color: #dcecf7;
    padding: 8px;
    border: 1px solid #000000;
    mso-border-alt: solid #000000 0.5pt;
}

/* ========================================================
   TABLE MAKLUMAT
======================================================== */

.info-table {
    width: 100%;
    border-collapse: collapse;
    mso-cellspacing: 0;
    empty-cells: show;
}

.info-table td {
    border: 1px solid #000000 !important;
    mso-border-alt: solid #000000 0.5pt;
    padding: 7px 8px;
    text-align: center;
    vertical-align: middle;
    height: 25px;
}

.info-label {
    font-weight: bold;
    color: #ffffff;
    background-color: #245b83;
}

.info-value {
    font-weight: bold;
    color: #1f2937;
    background-color: #f4f8fb;
}

.ave-title {
    font-weight: bold;
    color: #ffffff;
    background-color: #2f7f8f;
}

.ave-value {
    font-weight: bold;
    color: #1f2937;
    background-color: #edf8f7;
}

/* ========================================================
   MAIN RECORD TABLE
======================================================== */

.record-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #000000;
    mso-cellspacing: 0;
    empty-cells: show;
    font-family: Calibri, Arial, sans-serif;
    font-size: 10pt;
    table-layout: auto;
}

/*
   Border ditetapkan pada semua TH dan TD.
   Ini termasuk cell kosong dan cell tanpa nilai.
*/

.record-table th,
.record-table td {
    border: 1px solid #000000 !important;
    mso-border-alt: solid #000000 0.5pt;
    padding: 6px 7px;
    text-align: center;
    vertical-align: middle;
    height: 24px;
}

/* Header */

.record-table th {
    background-color: #245b83;
    color: #ffffff;
    font-weight: bold;
    white-space: nowrap;
}

.record-table th:first-child {
    background-color: #163a5f;
}

.record-table th:nth-child(2) {
    background-color: #2f7f8f;
}

.record-table th:last-child {
    background-color: #163a5f;
}

/* Cell kosong */

.record-table td:empty {
    border: 1px solid #000000 !important;
    mso-border-alt: solid #000000 0.5pt;
    background-color: #ffffff;
    height: 24px;
}

/* Nama item */

.item-name {
    text-align: left !important;
    font-weight: bold;
    color: #17324d;
    background-color: #eaf2f8 !important;
    white-space: nowrap;
}

/* Opening balance */

.opening-cell {
    font-weight: bold;
    color: #155e75;
    background-color: #eef9fb !important;
}

/* Closing balance */

.closing-cell {
    font-weight: bold;
    color: #163a5f;
    background-color: #e8f0f7 !important;
}

/* ========================================================
   OUT + BALANCE POWDER
======================================================== */

.bold-border td {
    border-top: 2px solid #000000 !important;
    border-bottom: 2px solid #000000 !important;
    mso-border-top-alt: solid #000000 1.5pt;
    mso-border-bottom-alt: solid #000000 1.5pt;
    font-weight: bold;
    background-color: #e7f3f5 !important;
    color: #123f4a;
}

.bold-border .item-name {
    background-color: #d5ebee !important;
}

/* ========================================================
   PHOENIX + DAIKO CLOSING
======================================================== */

.merged-closing {
    font-weight: bold;
    color: #163a5f;
    background-color: #dcecf7 !important;
    border: 1px solid #000000 !important;
    mso-border-alt: solid #000000 0.5pt;
}

/* ========================================================
   CELL NO DATE
======================================================== */

.no-date {
    border: 1px solid #000000 !important;
    mso-border-alt: solid #000000 0.5pt;
    padding: 10px;
    text-align: center;
    color: #64748b;
    background-color: #f5f9fc;
    font-style: italic;
}

</style>
</head>

<body>

<!-- =====================================================
     TITLE TABLE
===================================================== -->

<table border="1" cellspacing="0" cellpadding="0" width="100%">

<tr>
    <td
        colspan="<?= count($date_data) + 3 ?>"
        class="title"
        style="border:1px solid #000000;"
    >
        WASTE RECYCLE RECORD MAIN
    </td>
</tr>

<tr>
    <td
        colspan="<?= count($date_data) + 3 ?>"
        class="subtitle"
        style="border:1px solid #000000;"
    >
        <?= htmlspecialchars($month_name) ?>
        <?= htmlspecialchars((string)$year) ?>
    </td>
</tr>

<tr>
    <td
        colspan="<?= count($date_data) + 3 ?>"
        style="border:1px solid #000000;height:10px;"
    >
        &nbsp;
    </td>
</tr>

</table>

<br>

<!-- =====================================================
     PRODUCTION INFORMATION
===================================================== -->

<table
    class="info-table"
    border="1"
    cellspacing="0"
    cellpadding="0"
    width="100%"
>

<tr>
    <td rowspan="3" class="info-label">
        Tombo No. 1995<br>
        Production Estimation Qty<br>
        [Kgs]
    </td>

    <td rowspan="3" class="info-value">
        <?= htmlspecialchars((string)$tombo_no) ?>
    </td>

    <td class="ave-title">
        Ave
    </td>

    <td rowspan="3" class="info-label">
        Month
    </td>

    <td rowspan="3" class="info-value">
        <?= htmlspecialchars($month_name) ?>
    </td>

    <td class="info-label">
        Working Days
    </td>
</tr>

<tr>
    <td class="ave-value">
        <?= formatNumber($ave_1) ?> %
    </td>

    <td rowspan="2" class="info-value">
        <?= htmlspecialchars((string)$working_days) ?>
    </td>
</tr>

<tr>
    <td class="ave-value">
        <?= formatNumber($ave_2) ?>
    </td>
</tr>

</table>

<br>

<!-- =====================================================
     MAIN RECORD TABLE
===================================================== -->

<table
    class="record-table"
    border="1"
    cellspacing="0"
    cellpadding="0"
>

<thead>
<tr>
    <th>Items</th>
    <th>Opening Balance</th>

    <?php foreach ($date_data as $date => $values): ?>

        <?php
        $dateObject = DateTime::createFromFormat('!Y-m-d', $date);

        $displayDate = $dateObject
            ? $dateObject->format('d/m/Y')
            : $date;
        ?>

        <th>
            <?= htmlspecialchars($displayDate) ?>
        </th>

    <?php endforeach; ?>

    <th>Closing Balance</th>
</tr>
</thead>

<tbody>

<?php foreach ($items as $key => $label): ?>

    <?php
    $rowClass = '';

    if ($key === 'out_qty' || $key === 'balance_powder') {
        $rowClass = 'bold-border';
    }
    ?>

    <tr class="<?= $rowClass ?>">

        <!-- ITEM NAME -->

        <td class="item-name">
            <?= htmlspecialchars($label) ?>
        </td>

        <!-- OPENING BALANCE -->

        <td class="opening-cell">
            <?php
            if ($key === 'out_qty') {
                echo '&nbsp;';
            } else {
                echo formatNumber($opening_balance[$key] ?? 0);
            }
            ?>
        </td>

        <!-- DAILY DATA -->

        <?php foreach ($date_data as $date => $values): ?>

            <td>
                <?php
                if ($key === 'out_qty') {

                    $phoenix = getDateValue(
                        $date_data,
                        $date,
                        'phoenix'
                    );

                    $daiko = getDateValue(
                        $date_data,
                        $date,
                        'daiko'
                    );

                    $outValue = 0;

                    if (is_numeric($phoenix)) {
                        $outValue += (float)$phoenix;
                    }

                    if (is_numeric($daiko)) {
                        $outValue += (float)$daiko;
                    }

                    echo formatNumber($outValue);

                } else {

                    $value = getDateValue(
                        $date_data,
                        $date,
                        $key
                    );

                    /*
                       Kekalkan cell kosong apabila data asal kosong.
                       Cell masih mempunyai border hitam.
                    */

                    if ($value === '') {
                        echo '&nbsp;';
                    } else {
                        echo formatNumber($value);
                    }
                }
                ?>
            </td>

        <?php endforeach; ?>

        <!-- CLOSING BALANCE -->

        <?php if ($key === 'phoenix'): ?>

            <td class="merged-closing" rowspan="2">
                <?= formatNumber($phoenixDaikoClosing) ?>
            </td>

        <?php elseif ($key === 'daiko'): ?>

            <?php
            // Closing Balance digabungkan dengan Phoenix.
            ?>

        <?php elseif ($key === 'out_qty'): ?>

            <td class="closing-cell">
                TOTAL
            </td>

        <?php else: ?>

            <?php
            $closing = 0;
            $valuesForClosing = [];

            foreach ($date_data as $date => $dateValues) {

                $value = getDateValue(
                    $date_data,
                    $date,
                    $key
                );

                if (is_numeric($value)) {
                    $valuesForClosing[] = (float)$value;
                }
            }

            /*
            SUM ITEMS
            */

            if (
                in_array($key, [
                    'from_production_theoretical',
                    'from_production_actual',
                    'crushing_process_theoretical',
                    'crushing_process_actual',
                    'in_theoretical',
                    'in_actual'
                ], true)
            ) {

                $closing = array_sum($valuesForClosing);

            /*
            LAST DATE VALUE
            */

            } elseif (
                in_array($key, [
                    'balance_waste_before_crush',
                    'balance_waste_after_crush',
                    'theoretical_powder',
                    'actual_powder',
                    'balance_powder'
                ], true)
            ) {

                if (!empty($valuesForClosing)) {
                    $closing = end($valuesForClosing);
                }

            } else {

                $closing = array_sum($valuesForClosing);
            }
            ?>

            <td class="closing-cell">
                <?= formatNumber($closing) ?>
            </td>

        <?php endif; ?>

    </tr>

<?php endforeach; ?>

</tbody>
</table>

<?php if (empty($date_data)): ?>

<br>

<table
    class="record-table"
    border="1"
    cellspacing="0"
    cellpadding="0"
>
<tr>
    <td class="no-date">
        No date has been added to this record.
    </td>
</tr>
</table>

<?php endif; ?>

</body>
</html>

<?php
exit;
?>