<?php

include __DIR__ . "/../db.php";


// =========================================================
// AMBIL MONTH & YEAR
// =========================================================

$month = isset($_GET['month'])
    ? (int)$_GET['month']
    : 0;

$year = isset($_GET['year'])
    ? (int)$_GET['year']
    : 0;


// =========================================================
// VALIDATION
// =========================================================

if ($month < 1 || $month > 12) {
    die("Invalid month.");
}

if ($year < 2000 || $year > 2100) {
    die("Invalid year.");
}


// =========================================================
// NAMA BULAN
// =========================================================

$month_names = [
    1  => "JANUARY",
    2  => "FEBRUARY",
    3  => "MARCH",
    4  => "APRIL",
    5  => "MAY",
    6  => "JUNE",
    7  => "JULY",
    8  => "AUGUST",
    9  => "SEPTEMBER",
    10 => "OCTOBER",
    11 => "NOVEMBER",
    12 => "DECEMBER"
];

$month_name = $month_names[$month];


// =========================================================
// TARIKH
// =========================================================

$start_date = sprintf(
    "%04d-%02d-01",
    $year,
    $month
);

$end_date = date(
    "Y-m-d",
    strtotime($start_date . " +1 month")
);


// =========================================================
// AMBIL DATA
// =========================================================

$sql = "
    SELECT
        id,
        name,
        shift,
        date,
        output_powder,
        qty_scarp
    FROM operator
    WHERE date >= ?
    AND date < ?
    ORDER BY date ASC, id ASC
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $start_date,
    $end_date
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die(
        "Unable to retrieve data: " .
        mysqli_stmt_error($stmt)
    );
}


// =========================================================
// SIMPAN DATA
// =========================================================

$records = [];

$total_powder = 0;
$total_scrap = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $records[] = $row;

    $total_powder += (float)$row['output_powder'];

    $total_scrap += (float)$row['qty_scarp'];
}


$total_records = count($records);


// =========================================================
// NAMA FILE
// =========================================================

$filename =
    "Operator_Record_" .
    $month_name .
    "_" .
    $year .
    ".xls";


// =========================================================
// EXCEL HEADER
// =========================================================

header(
    "Content-Type: application/vnd.ms-excel; charset=UTF-8"
);

header(
    "Content-Disposition: attachment; filename=\"$filename\""
);

header("Pragma: no-cache");

header("Expires: 0");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<style>

/* =========================================================
   GENERAL
========================================================= */

body {

    margin: 20px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    color: #222;

}


/* =========================================================
   MAIN TITLE
========================================================= */

.title {

    background-color: #1e1768;

    color: #ffffff;

    font-family:
        Aptos Display,
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 22px;

    font-weight: 700;

    text-align: center;

    padding: 14px 10px;

}


/* =========================================================
   SUB TITLE
========================================================= */

.subtitle {

    background-color: #243b7a;

    color: #ffffff;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 15px;

    font-weight: 600;

    text-align: center;

    padding: 9px 10px;

}


/* =========================================================
   INFORMATION TABLE
========================================================= */

.info {

    width: 100%;

    margin-top: 12px;

    margin-bottom: 12px;

    border-collapse: collapse;

}


.info td {

    padding: 7px 10px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 12px;

}


.info-label {

    width: 140px;

    background-color: #dce6f8;

    font-weight: 600;

    color: #243b7a;

}


.info-value {

    font-weight: 600;

    color: #222;

}


/* =========================================================
   MAIN DATA TABLE
========================================================= */

table.data {

    width: 100%;

    border-collapse: collapse;

    margin-top: 8px;

}


/* =========================================================
   TABLE HEADER
========================================================= */

table.data th {

    background-color: #243b7a;

    color: #ffffff;

    border: 1px solid #17275c;

    padding: 9px 8px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 12px;

    font-weight: 700;

    text-align: center;

    vertical-align: middle;

}


/* =========================================================
   TABLE BODY
========================================================= */

table.data td {

    border: 1px solid #9aa7c4;

    padding: 8px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 11.5px;

    vertical-align: middle;

}


/* =========================================================
   COLUMN STYLE
========================================================= */

.no {

    width: 55px;

    text-align: center;

}


.name {

    text-align: left;

    font-weight: 500;

}


.shift {

    width: 100px;

    text-align: center;

}


.date {

    width: 110px;

    text-align: center;

}


.number {

    width: 140px;

    text-align: right;

}


/* =========================================================
   TOTAL ROW
========================================================= */

.total td {

    background-color: #080e46;

    color: #ffffff;

    border: 1px solid #080e46;

    padding: 10px 8px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 12px;

    font-weight: 700;

}


.total-label {

    text-align: right;

}


/* =========================================================
   EMPTY DATA
========================================================= */

.empty {

    text-align: center;

    padding: 20px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 12px;

    font-weight: 600;

    color: #666666;

}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    margin-top: 15px;

    font-family:
        Aptos,
        "Segoe UI",
        Arial,
        sans-serif;

    font-size: 10px;

    color: #666666;

    text-align: right;

}

</style>

</head>


<body>



<!-- ======================================================
     HEADER EXCEL
====================================================== -->

<table
    width="100%"
    cellspacing="0"
    cellpadding="0"
    style="border-collapse: collapse;"
>

    <!-- WASTE RECYCLE -->
    <tr>

        <td
            colspan="6"
            style="
                background-color:#1e1768;
                color:#ffffff;
                font-family:Aptos, 'Segoe UI', Arial, sans-serif;
                font-size:22px;
                font-weight:bold;
                text-align:center;
                padding:14px;
                border:1px solid #1e1768;
            "
        >

            WASTE RECYCLE

        </td>

    </tr>


    <!-- OPERATOR RECORD -->
    <tr>

        <td
            colspan="6"
            style="
                background-color:#243b7a;
                color:#ffffff;
                font-family:Aptos, 'Segoe UI', Arial, sans-serif;
                font-size:15px;
                font-weight:bold;
                text-align:center;
                padding:10px;
                border:1px solid #243b7a;
            "
        >

            OPERATOR RECORD - <?= htmlspecialchars($month_name . " " . $year) ?>

        </td>

    </tr>


    <!-- EMPTY SPACE -->
    <tr>

        <td
            colspan="6"
            style="
                height:10px;
                border:none;
                font-size:5px;
            "
        >
            &nbsp;
        </td>

    </tr>


    <!-- MONTH -->
    <tr>

        <td
            colspan="6"
            style="
                font-family:Aptos, 'Segoe UI', Arial, sans-serif;
                font-size:12px;
                font-weight:bold;
                color:#243b7a;
                padding:6px 10px;
                border:none;
                text-align:left;
            "
        >

            Month:
            <?= htmlspecialchars($month_name . " " . $year) ?>

        </td>

    </tr>


    <!-- TOTAL RECORDS -->
    <tr>

        <td
            colspan="6"
            style="
                font-family:Aptos, 'Segoe UI', Arial, sans-serif;
                font-size:12px;
                font-weight:bold;
                color:#243b7a;
                padding:6px 10px;
                border:none;
                text-align:left;
            "
        >

            Total Records:
            <?= $total_records ?>

        </td>

    </tr>


    <!-- SPACE BEFORE TABLE -->
    <tr>

        <td
            colspan="6"
            style="
                height:8px;
                border:none;
                font-size:5px;
            "
        >
            &nbsp;
        </td>

    </tr>

</table>

<!-- ======================================================
     DATA TABLE
====================================================== -->

<table class="data">

<thead>

<tr>

<th class="no">

    No.

</th>

<th>

    Name

</th>

<th class="shift">

    Shift

</th>

<th class="date">

    Date

</th>

<th class="number">

    Qty Powder (kg)

</th>

<th class="number">

    Qty Scrap (kg)

</th>

</tr>

</thead>


<tbody>


<?php if ($total_records > 0): ?>


<?php

$no = 1;

foreach ($records as $row):

?>


<tr>


<!-- NO -->

<td class="no">

    <?= $no++ ?>

</td>


<!-- NAME -->

<td class="name">

    <?= htmlspecialchars(
        $row['name']
    ) ?>

</td>


<!-- SHIFT -->

<td class="shift">

    <?= htmlspecialchars(
        $row['shift']
    ) ?>

</td>


<!-- DATE -->

<td class="date">

    <?= htmlspecialchars(
        date(
            "d/m/Y",
            strtotime($row['date'])
        )
    ) ?>

</td>


<!-- QTY POWDER -->

<td class="number">

    <?= number_format(
        (float)$row['output_powder'],
        2
    ) ?>

</td>


<!-- QTY SCRAP -->

<td class="number">

    <?= number_format(
        (float)$row['qty_scarp'],
        2
    ) ?>

</td>


</tr>


<?php endforeach; ?>


<!-- ======================================================
     TOTAL
====================================================== -->

<tr class="total">


<td colspan="4" class="total-label">

    TOTAL

</td>


<td class="number">

    <?= number_format(
        $total_powder,
        2
    ) ?>

</td>


<td class="number">

    <?= number_format(
        $total_scrap,
        2
    ) ?>

</td>


</tr>


<?php else: ?>


<tr>

<td colspan="6" class="empty">

    No operator records found for
    <?= htmlspecialchars(
        $month_name . " " . $year
    ) ?>.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>


<!-- ======================================================
     FOOTER
====================================================== -->

<div class="footer">

    Generated on:
    <?= date("d/m/Y h:i A") ?>

</div>


</body>

</html>


<?php

mysqli_stmt_close($stmt);

?>