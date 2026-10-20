
<?php

session_start();

include "../db.php";


/*
=========================================================
CHECK ADMIN LOGIN
=========================================================
*/

if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {

    header("Location: ../login.php");
    exit;

}


/*
=========================================================
TOTAL ADMIN RECORDS
=========================================================
*/

$admin_total = 0;

$sql_admin = "SELECT COUNT(*) AS total FROM admin";

$result_admin = mysqli_query($conn, $sql_admin);

if ($result_admin) {

    $row_admin = mysqli_fetch_assoc($result_admin);

    $admin_total = (int)$row_admin['total'];

}


/*
=========================================================
OPERATOR RECORDS - CURRENT MONTH
=========================================================
*/

$current_month_start = date('Y-m-01');

$next_month_start = date(
    'Y-m-01',
    strtotime('+1 month')
);

$operator_total = 0;

$sql_operator = "
    SELECT COUNT(*) AS total
    FROM operator
    WHERE date >= '$current_month_start'
    AND date < '$next_month_start'
";

$result_operator = mysqli_query($conn, $sql_operator);

if ($result_operator) {

    $row_operator = mysqli_fetch_assoc($result_operator);

    $operator_total = (int)$row_operator['total'];

}


/*
=========================================================
RECENT ADMIN RECORDS
=========================================================
*/

$recent_records = [];

$sql_recent = "
    SELECT id, month, year
    FROM admin
    ORDER BY id DESC
    LIMIT 5
";

$result_recent = mysqli_query($conn, $sql_recent);

if ($result_recent) {

    while ($row = mysqli_fetch_assoc($result_recent)) {

        $recent_records[] = $row;

    }

}


/*
=========================================================
CURRENT MONTH NAME
=========================================================
*/

$current_month_name = date('F Y');

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard - Waste Recycle</title>


<!-- FONT AWESOME -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


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

    font-family: Arial, Helvetica, sans-serif;

    background: #f5f6fa;

    color: #222;

}


/* =====================================================
   SIDEBAR
   SAME AS ADMIN FORM
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


/* =====================================================
   LOGO
===================================================== */

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


/* =====================================================
   SYSTEM TITLE
===================================================== */

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


/* =====================================================
   SIDEBAR LINE
===================================================== */

.sidebar-line {

    height: 1px;

    background: rgba(255,255,255,.25);

    margin: 0 18px 18px;

}


/* =====================================================
   MENU
===================================================== */

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


/* =====================================================
   MENU ICON
===================================================== */

.menu-icon {

    width: 22px;

    text-align: center;

    font-size: 18px;

}


/* =====================================================
   LOGOUT
===================================================== */

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
   MAIN CONTENT
===================================================== */

.main-content {

    margin-left: 235px;

    min-height: 100vh;

    padding: 35px 45px 50px;

}


/* =====================================================
   WELCOME
===================================================== */

.welcome {

    text-align: center;

    margin-bottom: 38px;

}

.welcome-small {

    font-size: 18px;

    color: #666;

    font-weight: 500;

    margin-bottom: 5px;

}

.welcome h1 {

    font-size: 38px;

    color: #1e1768;

    font-weight: 800;

    letter-spacing: 1px;

}

.welcome-main {

    font-size: 18px;

    color: #777;

    margin-top: 6px;

}


/* =====================================================
   SECTION TITLE
===================================================== */

.section-title {

    font-size: 20px;

    font-weight: 700;

    color: #222;

    margin-bottom: 18px;

}


/* =====================================================
   SUMMARY CARDS
===================================================== */

.summary-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 22px;

    margin-bottom: 35px;

}

.summary-card {

    background: white;

    border-radius: 12px;

    padding: 24px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.07);

    display: flex;

    align-items: center;

    gap: 20px;

    min-height: 125px;

    transition: 0.2s;

}

.summary-card:hover {

    transform: translateY(-2px);

    box-shadow: 0 5px 16px rgba(0,0,0,0.10);

}


/* =====================================================
   SUMMARY ICON
===================================================== */

.summary-icon {

    width: 58px;

    height: 58px;

    border-radius: 12px;

    background: #e8edff;

    color: #243b7a;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 23px;

    flex-shrink: 0;

}


/* =====================================================
   SUMMARY CONTENT
===================================================== */

.summary-content p {

    font-size: 14px;

    color: #666;

    margin-bottom: 5px;

}

.summary-content h2 {

    font-size: 32px;

    color: #1e1768;

    margin-bottom: 5px;

}

.summary-content span {

    display: block;

    font-size: 12px;

    color: #888;

}


/* =====================================================
   QUICK ACCESS
===================================================== */

.quick-title {

    font-size: 20px;

    font-weight: 700;

    color: #222;

    margin-bottom: 18px;

}

.quick-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 22px;

    margin-bottom: 40px;

}

.quick-card {

    background: white;

    border-radius: 12px;

    padding: 22px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.07);

    text-decoration: none;

    color: #222;

    display: flex;

    align-items: center;

    gap: 18px;

    transition: 0.2s;

}

.quick-card:hover {

    transform: translateY(-2px);

    box-shadow: 0 5px 16px rgba(0,0,0,0.10);

}

.quick-icon {

    width: 50px;

    height: 50px;

    border-radius: 10px;

    background: #e8edff;

    color: #243b7a;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}

.quick-card h3 {

    font-size: 15px;

    margin-bottom: 4px;

    color: #222;

}

.quick-card p {

    font-size: 12px;

    color: #777;

}


/* =====================================================
   RECENT RECORDS
===================================================== */

.recent-section {

    margin-top: 5px;

}

.recent-title {

    font-size: 20px;

    font-weight: 700;

    color: #222;

    margin-bottom: 18px;

}

.records-box {

    background: white;

    border-radius: 12px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.07);

    overflow: hidden;

    width: 80%;

    margin: 0 auto;

}

.records-table {

    width: 100%;

    border-collapse: collapse;

}

.records-table th {

    background: #f7f8fc;

    color: #555;

    font-size: 13px;

    text-align: left;

    padding: 12px 16px;

    border-bottom: 1px solid #eee;

}

.records-table td {

    padding: 13px 16px;

    font-size: 14px;

    border-bottom: 1px solid #eee;

}

.records-table tr:last-child td {

    border-bottom: none;

}

.records-table tr:hover {

    background: #fafbff;

}


/* =====================================================
   VIEW BUTTON
===================================================== */

.view-btn {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    background: #243b7a;

    color: white;

    text-decoration: none;

    padding: 7px 12px;

    border-radius: 6px;

    font-size: 12px;

    transition: 0.2s;

}

.view-btn:hover {

    background: #1e1768;

}


/* =====================================================
   NO RECORD
===================================================== */

.no-record {

    text-align: center;

    padding: 25px;

    color: #888;

    font-size: 14px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 900px) {

    .main-content {

        padding: 30px 25px;

    }

    .summary-grid {

        grid-template-columns: 1fr;

    }

    .quick-grid {

        grid-template-columns: 1fr;

    }

    .records-box {

        width: 100%;

    }

}


@media (max-width: 600px) {

    .sidebar {

        width: 190px;

    }

    .main-content {

        margin-left: 190px;

        padding: 25px 15px;

    }

    .welcome h1 {

        font-size: 28px;

    }

    .records-box {

        overflow-x: auto;

    }

    .records-table {

        min-width: 550px;

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

        <a
            href="dashboard.php"
            class="active"
        >

            <span class="menu-icon">
                <i class="fa-solid fa-chart-pie"></i>
            </span>

            <span>Dashboard</span>

        </a>


        <a href="admin_form.php">

            <span class="menu-icon">
                <i class="fa-solid fa-file-pen"></i>
            </span>

            <span>Admin Form</span>

        </a>


        <a href="admin_record.php">

            <span class="menu-icon">
                <i class="fa-solid fa-folder-open"></i>
            </span>

            <span>Admin Records</span>

        </a>


        <a href="operator_record.php">

            <span class="menu-icon">
                <i class="fa-solid fa-users-gear"></i>
            </span>

            <span>Operator Records</span>

        </a>

    </div>


    <div class="logout">

        <a href="../logout.php">

            <span class="menu-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </span>

            <span>Logout</span>

        </a>

    </div>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">


    <!-- WELCOME -->

    <div class="welcome">

        <div class="welcome-small">
            Welcome To
        </div>

        <h1>WASTE RECYCLE</h1>

        <div class="welcome-main">
            Management System
        </div>

    </div>


    <!-- =================================================
         SUMMARY
    ================================================== -->

    <div class="section-title">

        Overview

    </div>


    <div class="summary-grid">


        <!-- ADMIN RECORDS -->

        <div class="summary-card">

            <div class="summary-icon">

                <i class="fa-solid fa-file-circle-check"></i>

            </div>


            <div class="summary-content">

                <p>Admin Records</p>

                <h2><?= $admin_total ?></h2>

                <span>Total Records</span>

            </div>

        </div>


        <!-- OPERATOR RECORDS -->

        <div class="summary-card">

            <div class="summary-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <div class="summary-content">

                <p>Operator Records</p>

                <h2><?= $operator_total ?></h2>

                <span><?= $current_month_name ?></span>

            </div>

        </div>


    </div>


    <!-- =================================================
         QUICK ACCESS
    ================================================== -->

    <div class="quick-title">

        Quick Access

    </div>


    <div class="quick-grid">


        <!-- ADMIN FORM -->

        <a href="admin_form.php" class="quick-card">

            <div class="quick-icon">

                <i class="fa-solid fa-file-pen"></i>

            </div>


            <div>

                <h3>Admin Form</h3>

                <p>Enter and manage waste recycle data</p>

            </div>

        </a>


        <!-- OPERATOR RECORDS -->

        <a href="operator_record.php" class="quick-card">

            <div class="quick-icon">

                <i class="fa-solid fa-users-gear"></i>

            </div>


            <div>

                <h3>Operator Records</h3>

                <p>View operator production records</p>

            </div>

        </a>


    </div>


    <!-- =================================================
         RECENT ADMIN RECORDS
    ================================================== -->

    <div class="recent-section">


        <div class="recent-title">

            Recent Admin Records

        </div>


        <div class="records-box">

            <?php if (!empty($recent_records)): ?>


                <table class="records-table">

                    <thead>

                        <tr>

                            <th>Month</th>

                            <th>Year</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($recent_records as $record): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($record['month']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($record['year']) ?>
                                </td>

                                <td>

                                    <a
                                        href="admin_view.php?month=<?= urlencode($record['month']) ?>&year=<?= urlencode($record['year']) ?>"
                                        class="view-btn"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        View

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <div class="no-record">

                    No admin records available.

                </div>

            <?php endif; ?>

        </div>

    </div>


</div>


</body>

</html>
