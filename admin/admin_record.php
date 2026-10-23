<?php

session_start();

include "../db.php";


/* =========================================================
   CHECK ADMIN LOGIN
========================================================= */

if (
    !isset($_SESSION['admin_login']) ||
    $_SESSION['admin_login'] !== true
) {
    header("Location: ../login.php");
    exit;
}


/* =========================================================
   SELECTED YEAR
========================================================= */

$currentYear = (int)date('Y');

$selectedYear =
    isset($_GET['year'])
        ? (int)$_GET['year']
        : $currentYear;

if ($selectedYear <= 0) {
    $selectedYear = $currentYear;
}


/* =========================================================
   DELETE ADMIN RECORD
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_record'])
) {

    $deleteMonth = $_POST['month'] ?? '';

    $deleteYear =
        isset($_POST['year'])
            ? (int)$_POST['year']
            : 0;


    if (
        $deleteMonth !== '' &&
        $deleteYear > 0
    ) {

        $stmtDelete = mysqli_prepare(
            $conn,
            "DELETE FROM admin
             WHERE month = ?
             AND year = ?"
        );


        if ($stmtDelete) {

            mysqli_stmt_bind_param(
                $stmtDelete,
                "si",
                $deleteMonth,
                $deleteYear
            );

            mysqli_stmt_execute(
                $stmtDelete
            );


            $affectedRows =
                mysqli_stmt_affected_rows(
                    $stmtDelete
                );


            mysqli_stmt_close(
                $stmtDelete
            );


            if ($affectedRows > 0) {

                header(
                    "Location: admin_record.php?year="
                    . urlencode($selectedYear)
                    . "&deleted=1"
                );

                exit;

            } else {

                header(
                    "Location: admin_record.php?year="
                    . urlencode($selectedYear)
                    . "&deleted=0"
                );

                exit;

            }

        }

    }


    header(
        "Location: admin_record.php?year="
        . urlencode($selectedYear)
        . "&deleted=0"
    );

    exit;
}


/* =========================================================
   DELETE MESSAGE
========================================================= */

$deleteMessage = "";

if (isset($_GET['deleted'])) {

    if ($_GET['deleted'] === '1') {

        $deleteMessage =
            "Record deleted successfully.";

    } else {

        $deleteMessage =
            "Record could not be deleted.";

    }
}


/* =========================================================
   MONTH ORDER
========================================================= */

$monthOrder = [

    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"

];


/* =========================================================
   AVAILABLE YEARS
========================================================= */

$availableYears = [];


$sqlYears = "

    SELECT DISTINCT year_value

    FROM (

        SELECT
            CAST(year AS UNSIGNED) AS year_value

        FROM admin

        WHERE year IS NOT NULL
          AND year != ''


        UNION


        SELECT
            YEAR(date) AS year_value

        FROM operator

        WHERE date IS NOT NULL

    ) AS years_table

    WHERE year_value IS NOT NULL
      AND year_value > 0

    ORDER BY year_value DESC

";


$resultYears =
    mysqli_query(
        $conn,
        $sqlYears
    );


if ($resultYears) {

    while (
        $rowYear =
            mysqli_fetch_assoc($resultYears)
    ) {

        $availableYears[] =
            (int)$rowYear['year_value'];

    }

}


/* =========================================================
   ADMIN RECORDS
========================================================= */

$adminRecords = [];


$stmtRecords = mysqli_prepare(
    $conn,
    "

    SELECT
        month,
        year,
        COUNT(*) AS total_record

    FROM admin

    WHERE month IS NOT NULL
      AND month != ''

      AND year IS NOT NULL
      AND year != ''

      AND year = ?

    GROUP BY month, year

    ORDER BY FIELD(

        month,

        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'

    )

    "
);


if ($stmtRecords) {

    mysqli_stmt_bind_param(
        $stmtRecords,
        "i",
        $selectedYear
    );

    mysqli_stmt_execute(
        $stmtRecords
    );


    $resultRecords =
        mysqli_stmt_get_result(
            $stmtRecords
        );


    while (
        $row =
            mysqli_fetch_assoc(
                $resultRecords
            )
    ) {

        $adminRecords[] =
            $row;

    }


    mysqli_stmt_close(
        $stmtRecords
    );

}


/* =========================================================
   SUMMARY INITIALIZATION
========================================================= */

$summary = [];


foreach (
    $monthOrder as $monthName
) {

    $summary[$monthName] = [

        'scrap' => 0,

        'powder' => 0,

        'usage' => 0

    ];

}


/* =========================================================
   OPERATOR POWDER + SCRAP
========================================================= */

$stmtOperator = mysqli_prepare(
    $conn,
    "

    SELECT

        MONTH(date) AS month_number,

        SUM(
            COALESCE(
                output_powder,
                0
            )
        ) AS total_powder,

        SUM(
            COALESCE(
                qty_scarp,
                0
            )
        ) AS total_scrap

    FROM operator

    WHERE date IS NOT NULL
      AND YEAR(date) = ?

    GROUP BY MONTH(date)

    ORDER BY MONTH(date)

    "
);


if ($stmtOperator) {

    mysqli_stmt_bind_param(
        $stmtOperator,
        "i",
        $selectedYear
    );

    mysqli_stmt_execute(
        $stmtOperator
    );


    $resultOperator =
        mysqli_stmt_get_result(
            $stmtOperator
        );


    while (
        $row =
            mysqli_fetch_assoc(
                $resultOperator
            )
    ) {

        $monthNumber =
            (int)$row['month_number'];


        if (
            $monthNumber >= 1 &&
            $monthNumber <= 12
        ) {

            $monthName =
                $monthOrder[
                    $monthNumber - 1
                ];


            $summary[$monthName]['powder'] =
                (float)(
                    $row['total_powder'] ?? 0
                );


            $summary[$monthName]['scrap'] =
                (float)(
                    $row['total_scrap'] ?? 0
                );

        }

    }


    mysqli_stmt_close(
        $stmtOperator
    );

}


/* =========================================================
   USAGE
   PHOENIX + DAIKO
========================================================= */

$stmtUsage = mysqli_prepare(
    $conn,
    "

    SELECT
        month,
        year,
        date_data

    FROM admin

    WHERE year = ?

      AND date_data IS NOT NULL

      AND date_data != ''

    "
);


if ($stmtUsage) {

    mysqli_stmt_bind_param(
        $stmtUsage,
        "i",
        $selectedYear
    );

    mysqli_stmt_execute(
        $stmtUsage
    );


    $resultUsage =
        mysqli_stmt_get_result(
            $stmtUsage
        );


    while (
        $row =
            mysqli_fetch_assoc(
                $resultUsage
            )
    ) {

        $adminMonth =
            $row['month'] ?? '';


        if (
            !isset(
                $summary[$adminMonth]
            )
        ) {

            continue;

        }


        $dateData =
            json_decode(
                $row['date_data'],
                true
            );


        if (
            !is_array($dateData)
        ) {

            continue;

        }


        foreach (
            $dateData as $dateKey => $values
        ) {

            if (
                $dateKey ===
                '__closing_balance__'
            ) {

                continue;

            }


            if (
                !is_array($values)
            ) {

                continue;

            }


            /* =========================================
               PHOENIX
            ========================================= */

            $phoenix = 0;


            $phoenixKeys = [

                'phoenix',
                'phoenix_qty',
                'Phoenix'

            ];


            foreach (
                $phoenixKeys as $key
            ) {

                if (
                    isset(
                        $values[$key]
                    ) &&
                    $values[$key] !== ''
                ) {

                    $phoenix =
                        (float)$values[$key];

                    break;

                }

            }


            /* =========================================
               DAIKO
            ========================================= */

            $daiko = 0;


            $daikoKeys = [

                'daiko',
                'daiko_qty',
                'DAIKO',
                'daiKo'

            ];


            foreach (
                $daikoKeys as $key
            ) {

                if (
                    isset(
                        $values[$key]
                    ) &&
                    $values[$key] !== ''
                ) {

                    $daiko =
                        (float)$values[$key];

                    break;

                }

            }


            /* =========================================
               USAGE
            ========================================= */

            $usage =
                $phoenix +
                $daiko;


            $summary[$adminMonth]['usage']
                += $usage;

        }

    }


    mysqli_stmt_close(
        $stmtUsage
    );

}


/* =========================================================
   CHART DATA
========================================================= */

$chartData = [];

$highestChartValue = 0;


foreach (
    $monthOrder as $monthName
) {

    $scrap =
        (float)$summary[
            $monthName
        ]['scrap'];


    $powder =
        (float)$summary[
            $monthName
        ]['powder'];


    $usage =
        (float)$summary[
            $monthName
        ]['usage'];


    $highestChartValue =
        max(
            $highestChartValue,
            $scrap,
            $powder,
            $usage
        );


    $chartData[] = [

        'month' =>
            substr(
                $monthName,
                0,
                3
            ),

        'scrap' =>
            $scrap,

        'powder' =>
            $powder,

        'usage' =>
            $usage

    ];

}


$chartMax =
    max(
        4000,
        (int)(
            ceil(
                $highestChartValue / 500
            ) * 500
        )
    );


/* =========================================================
   CHART ARRAYS
========================================================= */

$chartLabels = [];

$scrapChartData = [];

$powderChartData = [];

$usageChartData = [];


foreach (
    $chartData as $data
) {

    $chartLabels[] =
        $data['month'];

    $scrapChartData[] =
        $data['scrap'];

    $powderChartData[] =
        $data['powder'];

    $usageChartData[] =
        $data['usage'];

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Admin Records - Waste Recycle
</title>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =====================================================
   BODY
===================================================== */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;

    color: #333;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 235px;

    height: 100vh;

    background: #1e1768;

    color: white;

    padding-top: 25px;

    z-index: 1000;

}


.logo-box {

    text-align: center;

    padding-bottom: 25px;

}


.logo-box img {

    width: 65px;

    height: 65px;

    object-fit: contain;

    background: white;

    padding: 5px;

    border-radius: 0;

}


.system-title {

    font-size: 17px;

    font-weight: bold;

    margin-top: 10px;

}


.system-subtitle {

    font-size: 12px;

    margin-top: 4px;

    opacity: .8;

}


.sidebar-line {

    height: 1px;

    background:
        rgba(255,255,255,.25);

    margin:
        0 18px 18px;

}


.menu {

    padding: 0 12px;

}


.menu a {

    display: flex;

    align-items: center;

    gap: 13px;

    color: white;

    text-decoration: none;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 7px;

    font-size: 14px;

}


.menu a:hover,
.menu a.active {

    background: #2f4da0;

}


.menu-icon {

    width: 22px;

    text-align: center;

    font-size: 18px;

}


.logout {

    position: absolute;

    bottom: 20px;

    left: 12px;

    right: 12px;

}


.logout a {

    display: flex;

    align-items: center;

    gap: 13px;

    color: white;

    text-decoration: none;

    padding: 13px 15px;

    border-radius: 7px;

    font-size: 14px;

}


.logout a:hover {

    background: #2f4da0;

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 235px;

    padding: 30px;

}


/* =====================================================
   HEADER
===================================================== */

.top-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-header h1 {

    color: #243b7a;

    font-size: 28px;

}


.page-header p {

    margin-top: 5px;

    color: #777;

    font-size: 14px;

}


.profile {

    display: flex;

    align-items: center;

    gap: 10px;

    color: #243b7a;

    font-size: 14px;

    font-weight: bold;

}


.profile i {

    font-size: 25px;

}


/* =====================================================
   ALERT
===================================================== */

.alert {

    padding: 12px 16px;

    border-radius: 7px;

    margin-bottom: 20px;

    background: #e8f7ee;

    color: #217a45;

    border: 1px solid #bce5ca;

    font-size: 13px;

}


/* =====================================================
   SUMMARY CARD
===================================================== */

.summary-card {

    background: white;

    padding: 18px 22px;

    border-radius: 9px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.07);

    margin-bottom: 25px;

}


.summary-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 15px;

}


.summary-title h2 {

    color: #243b7a;

    font-size: 20px;

}


.summary-title p {

    margin-top: 5px;

    color: #777;

    font-size: 13px;

}


.year-form {

    display: flex;

    align-items: center;

    gap: 8px;

}


.year-form label {

    font-size: 13px;

    font-weight: bold;

    color: #555;

}


.year-form select {

    height: 36px;

    border: 1px solid #d2d7df;

    border-radius: 5px;

    padding:
        5px 30px 5px 10px;

    background: white;

    color: #333;

}


.year-form button {

    height: 36px;

    padding: 0 15px;

    border: none;

    border-radius: 5px;

    background: #243b7a;

    color: white;

    font-weight: bold;

    cursor: pointer;

}


.year-form button:hover {

    background: #2f4da0;

}


/* =====================================================
   CHART
===================================================== */

.chart-wrapper {

    position: relative;

    height: 430px;

    width: 100%;

}


/* =====================================================
   RECORDS SECTION
===================================================== */

.records-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px
        rgba(0,0,0,0.07);

}


.records-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.records-header h2 {

    color: #243b7a;

    font-size: 20px;

}


.records-header p {

    color: #777;

    font-size: 13px;

    margin-top: 5px;

}


/* =====================================================
   MONTH GRID
===================================================== */

.month-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 18px;

}


.month-card {

    border: 1px solid #e0e4eb;

    border-radius: 10px;

    padding: 18px;

    background: #fafbfc;

    transition: .2s;

}


.month-card:hover {

    border-color: #bfc9dd;

    box-shadow:
        0 3px 10px
        rgba(0,0,0,.05);

    transform: translateY(-2px);

}


.month-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 14px;

}


.folder-icon {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #eaf0ff;

    color: #243b7a;

    font-size: 21px;

}


.delete-form {

    margin: 0;

}


.delete-btn {

    width: 34px;

    height: 34px;

    border: none;

    border-radius: 6px;

    background: #fff0f0;

    color: #d9534f;

    cursor: pointer;

}


.delete-btn:hover {

    background: #d9534f;

    color: white;

}


.month-name {

    color: #243b7a;

    font-size: 16px;

    font-weight: bold;

}


.month-year {

    color: #888;

    font-size: 12px;

    margin-top: 4px;

}


.record-count {

    margin-top: 12px;

    font-size: 12px;

    color: #666;

}


.view-btn {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    margin-top: 15px;

    text-decoration: none;

    color: white;

    background: #243b7a;

    padding: 8px 13px;

    border-radius: 5px;

    font-size: 12px;

    font-weight: bold;

}


.view-btn:hover {

    background: #2f4da0;

}


/* =====================================================
   NO RECORD
===================================================== */

.no-record {

    text-align: center;

    padding: 45px 20px;

    color: #888;

}


.no-record i {

    font-size: 45px;

    margin-bottom: 12px;

    color: #c7cbd4;

}


.no-record p {

    font-size: 14px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1050px) {

    .month-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 750px) {

    .sidebar {

        width: 200px;

    }


    .main {

        margin-left: 200px;

        padding: 20px;

    }


    .summary-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .month-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 560px) {

    .sidebar {

        width: 70px;

    }


    .logo-box {

        padding-bottom: 15px;

    }


    .logo-box img {

        width: 45px;

        height: 45px;

    }


    .system-title,
    .system-subtitle,
    .sidebar-line,
    .menu a span:not(.menu-icon),
    .logout a span:not(.menu-icon) {

        display: none;

    }


    .menu {

        padding: 0 10px;

    }


    .menu a,
    .logout a {

        justify-content: center;

        padding: 13px 5px;

    }


    .main {

        margin-left: 70px;

        padding: 15px;

    }


    .page-header h1 {

        font-size: 22px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">


    <div class="logo-box">

        <img
            src="../img/LogoNichias.jpg"
            alt="NICHIAS Logo"
        >


        <div class="system-title">

            ADMIN SYSTEM

        </div>


        <div class="system-subtitle">

            Waste Recycle

        </div>

    </div>


    <div class="sidebar-line"></div>


    <div class="menu">


        <a href="dashboard.php">

            <span class="menu-icon">

                <i class="fa-solid fa-chart-pie"></i>

            </span>


            <span>

                Dashboard

            </span>

        </a>


        <a href="admin_form.php">

            <span class="menu-icon">

                <i class="fa-solid fa-file-pen"></i>

            </span>


            <span>

                Admin Form

            </span>

        </a>


        <a
            href="admin_record.php"
            class="active"
        >

            <span class="menu-icon">

                <i class="fa-solid fa-folder-open"></i>

            </span>


            <span>

                Admin Records

            </span>

        </a>


        <a href="operator_record.php">

            <span class="menu-icon">

                <i class="fa-solid fa-users-gear"></i>

            </span>


            <span>

                Operator Records

            </span>

        </a>


    </div>


  <div class="logout">
    <a href="../login.php"
       onclick="return confirm('Are you sure you want to logout?');">
        <span class="menu-icon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </span>
        <span>Logout</span>
    </a>
</div>


</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="top-header">


        <div class="page-header">

            <h1>

                Admin Records

            </h1>


            <p>

                View and manage Waste Recycle production records.

            </p>

        </div>


    </div>


    <?php if ($deleteMessage !== ""): ?>


        <div class="alert">

            <i
                class="fa-solid fa-circle-check"
            ></i>


            <?php

            echo htmlspecialchars(
                $deleteMessage
            );

            ?>

        </div>


    <?php endif; ?>


    <!-- =================================================
         SUMMARY
    ================================================== -->

    <div class="summary-card">


        <div class="summary-header">


            <div class="summary-title">

                <h2>

                    <?= htmlspecialchars(
                        $selectedYear
                    ) ?>

                    SUMMARY

                </h2>


                <p>

                    Powder, Scrap & Usage by Month

                </p>

            </div>


            <form
                method="GET"
                class="year-form"
            >


                <label for="year">

                    Year

                </label>


                <select
                    name="year"
                    id="year"
                >


                    <?php

                    if (
                        !in_array(
                            $selectedYear,
                            $availableYears
                        )
                    ) {

                        $availableYears[] =
                            $selectedYear;

                        rsort(
                            $availableYears
                        );

                    }


                    foreach (
                        $availableYears
                        as $year
                    ):

                    ?>


                        <option
                            value="<?= $year ?>"

                            <?= (
                                $year ===
                                $selectedYear
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= $year ?>

                        </option>


                    <?php endforeach; ?>


                </select>


                <button
                    type="submit"
                >

                    <i
                        class="fa-solid fa-filter"
                    ></i>

                    View

                </button>


            </form>


        </div>


        <!-- =================================================
             CHART
        ================================================== -->

        <div class="chart-wrapper">

            <canvas
                id="summaryChart"
            ></canvas>

        </div>


    </div>


    <!-- =================================================
         ADMIN RECORDS
    ================================================== -->

    <div class="records-card">


        <div class="records-header">


            <div>

                <h2>

                    Admin Records

                </h2>


                <p>

                    Monthly Waste Recycle records for

                    <?= htmlspecialchars(
                        $selectedYear
                    ) ?>

                </p>

            </div>


        </div>


        <?php if (
            count($adminRecords) > 0
        ): ?>


            <div class="month-grid">


                <?php foreach (
                    $adminRecords
                    as $record
                ): ?>


                    <div class="month-card">


                        <div class="month-top">


                            <div class="folder-icon">

                                <i
                                    class="fa-solid fa-folder-open"
                                ></i>

                            </div>


                            <form
                                method="POST"
                                class="delete-form"

                                onsubmit="
                                    return confirm(
                                        'Are you sure you want to delete this record?'
                                    );
                                "
                            >


                                <input
                                    type="hidden"

                                    name="month"

                                    value="<?=
                                        htmlspecialchars(
                                            $record['month']
                                        )
                                    ?>"
                                >


                                <input
                                    type="hidden"

                                    name="year"

                                    value="<?=
                                        htmlspecialchars(
                                            $record['year']
                                        )
                                    ?>"
                                >


                                <button
                                    type="submit"

                                    name="delete_record"

                                    class="delete-btn"

                                    title="Delete Record"
                                >

                                    <i
                                        class="fa-solid fa-trash"
                                    ></i>

                                </button>


                            </form>


                        </div>


                        <div class="month-name">

                            <?= htmlspecialchars(
                                $record['month']
                            ) ?>

                        </div>


                        <div class="month-year">

                            Year

                            <?= htmlspecialchars(
                                $record['year']
                            ) ?>

                        </div>


                        <div class="record-count">

                            <i
                                class="fa-solid fa-file-lines"
                            ></i>


                            <?= (int)$record['total_record'] ?>


                            Record

                        </div>


                        <a
                            href="admin_view.php?month=<?=
                                urlencode(
                                    $record['month']
                                )
                            ?>&year=<?=
                                urlencode(
                                    $record['year']
                                )
                            ?>"

                            class="view-btn"
                        >

                            <i
                                class="fa-solid fa-eye"
                            ></i>


                            View Record

                        </a>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="no-record">


                <i
                    class="fa-solid fa-folder-open"
                ></i>


                <p>

                    No admin records found for

                    <?= htmlspecialchars(
                        $selectedYear
                    ) ?>.

                </p>


            </div>


        <?php endif; ?>


    </div>


</div>


<script>

/* =========================================================
   CHART DATA
========================================================= */

const chartLabels =
    <?= json_encode(
        $chartLabels
    ) ?>;


const scrapData =
    <?= json_encode(
        $scrapChartData
    ) ?>;


const powderData =
    <?= json_encode(
        $powderChartData
    ) ?>;


const usageData =
    <?= json_encode(
        $usageChartData
    ) ?>;


const chartMaximum =
    <?= (int)$chartMax ?>;


/* =========================================================
   CHART
========================================================= */

const ctx =
    document
        .getElementById(
            "summaryChart"
        )
        .getContext("2d");


new Chart(ctx, {

    type: "bar",


    data: {

        labels: chartLabels,


        datasets: [

            {

                label: "Powder",

                data: powderData,

                backgroundColor:
                    "#3B82F6",

                borderColor:
                    "#2563EB",

                borderWidth: 1,

                borderRadius: 2,

                borderSkipped: false,

                barThickness: 14

            },


            {

                label: "Scrap",

                data: scrapData,

                backgroundColor:
                    "#F59E0B",

                borderColor:
                    "#D97706",

                borderWidth: 1,

                borderRadius: 2,

                borderSkipped: false,

                barThickness: 14

            },


            {

                label: "Usage",

                data: usageData,

                backgroundColor:
                    "#10B981",

                borderColor:
                    "#059669",

                borderWidth: 1,

                borderRadius: 2,

                borderSkipped: false,

                barThickness: 14

            }

        ]

    },


    options: {

        responsive: true,

        maintainAspectRatio: false,


        plugins: {


            legend: {

                position: "top",

                labels: {

                    usePointStyle: false,

                    padding: 15,

                    font: {

                        size: 13,

                        weight: "600"

                    }

                }

            },


            tooltip: {

                backgroundColor:
                    "#1e1768",

                padding: 12,


                callbacks: {

                    label:
                        function(context) {

                            return " "
                                + context.dataset.label
                                + ": "
                                + Number(
                                    context.parsed.y
                                ).toLocaleString(
                                    "en-US",
                                    {
                                        maximumFractionDigits: 2
                                    }
                                );

                        }

                }

            }

        },


        scales: {


            x: {

                stacked: false,

                grid: {

                    display: false

                },


                ticks: {

                    font: {

                        size: 12,

                        weight: "600"

                    }

                }

            },


            y: {

                beginAtZero: true,

                min: 0,

                max: chartMaximum,


                ticks: {

                    stepSize: 500,

                    font: {

                        size: 11

                    },


                    callback:
                        function(value) {

                            return Number(
                                value
                            ).toLocaleString();

                        }

                },


                /* Quantity DIBUANG */

                title: {

                    display: false

                },


                grid: {

                    color:
                        "rgba(0,0,0,0.07)"

                }

            }

        }

    }

});

</script>


</body>

</html>