<?php

include "../db.php";


// ==================================================
// GET ALL OPERATOR RECORDS
// ==================================================

$sql = "SELECT * FROM operator ORDER BY date DESC, id DESC";

$result = mysqli_query($conn, $sql);


// ==================================================
// GROUP RECORDS BY MONTH
// ==================================================

$monthlyRecords = [];

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $date = $row['date'];

        $monthKey = date('Y-m', strtotime($date));

        if (!isset($monthlyRecords[$monthKey])) {

            $monthlyRecords[$monthKey] = [];

        }

        $monthlyRecords[$monthKey][] = $row;

    }

}


// ==================================================
// SORT MONTH - NEWEST FIRST
// ==================================================

uksort($monthlyRecords, function ($a, $b) {

    return strcmp($b, $a);

});


// ==================================================
// MONTH NAME
// ==================================================

$monthNames = [

    1 => "JANUARY",
    2 => "FEBRUARY",
    3 => "MARCH",
    4 => "APRIL",
    5 => "MAY",
    6 => "JUNE",
    7 => "JULY",
    8 => "AUGUST",
    9 => "SEPTEMBER",
    10 => "OCTOBER",
    11 => "NOVEMBER",
    12 => "DECEMBER"

];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Operator Records- Waste Recycle</title>


<!-- ==================================================
     FONT AWESOME
================================================== -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* ==================================================
   RESET
================================================== */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

    font-family: Arial, sans-serif;

}


/* ==================================================
   BODY
================================================== */

body {

    background: #f5f6fa;

    color: #333;

}


/* ==================================================
   CONTAINER
================================================== */

.container {

    display: flex;

    min-height: 100vh;

}


/* ==================================================
   SIDEBAR
================================================== */

.sidebar {

    width: 235px;

    min-height: 100vh;

    background: #1e1768;

    color: white;

    position: fixed;

    left: 0;

    top: 0;

    bottom: 0;

    flex-shrink: 0;

    overflow-y: auto;

}


/* ==================================================
   LOGO AREA
================================================== */

.logo-area {

    height: 145px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 15px;

    border-bottom: 1px solid rgba(255,255,255,0.18);

}


/* ==================================================
   LOGO
================================================== */

.logo-area img {

    width: 72px;

    height: 72px;

    object-fit: contain;

    background: #fff;

    padding: 5px;

    border-radius: 0;

    display: block;

}


/* ==================================================
   SIDEBAR TITLE
================================================== */

.sidebar-title {

    text-align: center;

    padding: 18px 10px 5px;

}


.sidebar-title h2 {

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 0.6px;

    line-height: 1.4;

}


.sidebar-title p {

    font-size: 10px;

    color: rgba(255,255,255,0.70);

    margin-top: 3px;

}


/* ==================================================
   SIDEBAR LINE
================================================== */

.sidebar-line {

    height: 1px;

    background: rgba(255,255,255,0.18);

    margin: 18px 18px 20px;

}


/* ==================================================
   MENU
================================================== */

.menu {

    padding: 0 12px;

    display: flex;

    flex-direction: column;

    gap: 5px;

}


.menu a {

    display: flex;

    align-items: center;

    gap: 13px;

    width: 100%;

    padding: 12px 14px;

    color: rgba(255,255,255,0.90);

    text-decoration: none;

    border-radius: 7px;

    font-size: 13.5px;

    font-weight: 600;

    transition: 0.2s;

}


.menu a i {

    width: 20px;

    text-align: center;

    font-size: 15px;

}


.menu a:hover {

    background: rgba(255,255,255,0.10);

    color: #fff;

}


.menu a.active {

    background: rgba(255,255,255,0.18);

    color: #fff;

}


/* ==================================================
   RIGHT SIDE
================================================== */

.right-side {

    flex: 1;

    min-width: 0;

    margin-left: 235px;

    display: flex;

    flex-direction: column;

}


/* ==================================================
   TOP HEADER
================================================== */

.top-header {

    width: 100%;

    height: 70px;

    background: white;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    padding: 0 35px;

    border-bottom: 1px solid #ddd;

    box-shadow: 0 2px 8px rgba(0,0,0,0.05);

    flex-shrink: 0;

}


/* ==================================================
   HEADER RIGHT
================================================== */

.header-right {

    display: flex;

    align-items: center;

    gap: 15px;

}


/* ==================================================
   OPERATOR PROFILE
================================================== */

.operator-profile {

    display: flex;

    align-items: center;

    gap: 10px;

    padding: 6px 18px 6px 6px;

    border: 1px solid #ddd;

    border-radius: 30px;

    background: white;

    font-size: 15px;

    font-weight: bold;

    color: #222;

}


/* ==================================================
   PROFILE ICON
================================================== */

.profile-icon {

    width: 35px;

    height: 35px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #e9eef0;

    color: #243b7a;

    font-size: 16px;

}


/* ==================================================
   LOGOUT BUTTON
================================================== */

.logout-btn {

    width: 45px;

    height: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    border: 1px solid #ddd;

    border-radius: 10px;

    background: white;

    color: #222;

    font-size: 18px;

    transition: 0.2s;

}


.logout-btn:hover {

    background: #f2f2f2;

}


/* ==================================================
   CONTENT
================================================== */

.content {

    flex: 1;

    padding: 40px 50px 50px;

}


/* ==================================================
   PAGE TITLE
================================================== */

.main-title {

    font-size: 28px;

    font-weight: bold;

    color: #243b7a;

    margin-bottom: 30px;

}


/* ==================================================
   MONTH CARD
================================================== */

.month-card {

    background: white;

    border: 1px solid #e0e2e7;

    border-radius: 10px;

    margin-bottom: 28px;

    overflow: hidden;

    box-shadow: 0 3px 10px rgba(0,0,0,0.04);

}


/* ==================================================
   MONTH HEADER
================================================== */

.month-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 16px 20px;

    background: #f1f3f8;

    border-bottom: 1px solid #dfe2e8;

}


/* ==================================================
   MONTH TITLE
================================================== */

.month-title {

    display: flex;

    align-items: center;

    gap: 10px;

    color: #243b7a;

    font-size: 15px;

    font-weight: bold;

}


.month-title i {

    font-size: 16px;

}


/* ==================================================
   RECORD COUNT
================================================== */

.record-count {

    font-size: 12px;

    color: #666;

    font-weight: 600;

}


/* ==================================================
   TABLE WRAPPER
================================================== */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}


/* ==================================================
   TABLE
================================================== */

.record-table {

    width: 100%;

    border-collapse: collapse;

    background: white;

}


/* ==================================================
   TABLE HEADER - BIRU TERANG
================================================== */

.record-table th {

    background: #080e46;

    color: white;

    font-size: 13px;

    font-weight: 700;

    padding: 13px 12px;

    text-align: center;

    border: 1px solid #c5d1d6;

    white-space: nowrap;

}


/* ==================================================
   TABLE BODY
================================================== */

.record-table td {

    padding: 12px;

    font-size: 13px;

    color: #333;

    text-align: center;

    border: 1px solid #d4d7df;

    white-space: nowrap;

}


/* ==================================================
   NAME ALIGNMENT
================================================== */

.record-table td.name-cell {

    text-align: left;

    font-weight: 600;

}


/* ==================================================
   ROW HOVER
================================================== */

.record-table tbody tr:hover {

    background: #f8f9fc;

}


/* ==================================================
   COLUMN WIDTH
================================================== */

.record-table th:nth-child(1),
.record-table td:nth-child(1) {

    width: 60px;

}


.record-table th:nth-child(2),
.record-table td:nth-child(2) {

    width: 180px;

}


.record-table th:nth-child(3),
.record-table td:nth-child(3) {

    width: 120px;

}


.record-table th:nth-child(4),
.record-table td:nth-child(4) {

    width: 120px;

}


.record-table th:nth-child(5),
.record-table td:nth-child(5) {

    width: 150px;

}


.record-table th:nth-child(6),
.record-table td:nth-child(6) {

    width: 150px;

}


.record-table th:nth-child(7),
.record-table td:nth-child(7) {

    width: 145px;

}


/* ==================================================
   ACTION
================================================== */

.action-cell {

    white-space: nowrap;

}


/* ==================================================
   EDIT BUTTON
================================================== */

.edit-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    padding: 7px 10px;

    background: #243b7a;

    color: white;

    text-decoration: none;

    border-radius: 5px;

    font-size: 11px;

    font-weight: 600;

}


.edit-btn:hover {

    background: #1e3065;

}


/* ==================================================
   DELETE BUTTON
================================================== */

.delete-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    padding: 7px 10px;

    background: #c62828;

    color: white;

    text-decoration: none;

    border-radius: 5px;

    font-size: 11px;

    font-weight: 600;

    margin-left: 4px;

}


.delete-btn:hover {

    background: #a91f1f;

}


/* ==================================================
   NO RECORD
================================================== */

.no-record {

    text-align: center;

    padding: 60px 20px;

    color: #777;

}


.no-record i {

    font-size: 35px;

    color: #c5c8d0;

    margin-bottom: 12px;

    display: block;

}


.no-record p {

    font-size: 14px;

}


/* ==================================================
   RESPONSIVE
================================================== */

@media (max-width: 900px) {

    .sidebar {

        width: 210px;

    }


    .right-side {

        margin-left: 210px;

    }


    .content {

        padding: 30px 25px 40px;

    }

}


@media (max-width: 700px) {

    .sidebar {

        width: 70px;

    }


    .right-side {

        margin-left: 70px;

    }


    .logo-area {

        height: 100px;

    }


    .logo-area img {

        width: 48px;

        height: 48px;

    }


    .sidebar-title,
    .sidebar-line {

        display: none;

    }


    .menu {

        padding: 15px 10px;

    }


    .menu a {

        justify-content: center;

        padding: 12px 5px;

    }


    .menu a span {

        display: none;

    }


    .menu a i {

        width: auto;

        font-size: 18px;

    }


    .top-header {

        height: 65px;

        padding: 0 15px;

    }


    .operator-profile {

        padding-right: 12px;

        font-size: 14px;

    }


    .profile-icon {

        width: 32px;

        height: 32px;

    }


    .logout-btn {

        width: 42px;

        height: 42px;

    }


    .content {

        padding: 25px 15px;

    }


    .main-title {

        font-size: 23px;

    }


}

</style>

</head>


<body>


<div class="container">


    <!-- ==================================================
         SIDEBAR
    ================================================== -->

    <div class="sidebar">


        <div class="logo-area">

            <img
                src="../img/LogoNichias.jpg"
                alt="NICHIAS Logo"
            >

        </div>


        <div class="sidebar-title">

            <h2>
                OPERATOR SYSTEM
            </h2>

            <p>
                Waste Recycle
            </p>

        </div>


        <div class="sidebar-line"></div>


        <div class="menu">


            <a href="form.php">

                <i class="fa-solid fa-file-pen"></i>

                <span>
                    Form
                </span>

            </a>


            <a
                href="record.php"
                class="active"
            >

                <i class="fa-solid fa-folder-open"></i>

                <span>
                    Records
                </span>

            </a>


        </div>


    </div>



    <!-- ==================================================
         RIGHT SIDE
    ================================================== -->

    <div class="right-side">


        <!-- TOP HEADER -->

        <div class="top-header">


            <div class="header-right">


                <div class="operator-profile">

                    <span class="profile-icon">

                        <i class="fa-solid fa-user"></i>

                    </span>

                    <span>
                        OPERATOR
                    </span>

                </div>


                <a
                    href="../login.php"
                    class="logout-btn"
                    title="Logout"
                >

                    <i class="fa-solid fa-right-from-bracket"></i>

                </a>


            </div>


        </div>



        <!-- CONTENT -->

        <div class="content">


            <div class="main-title">

                Operator Records

            </div>



            <?php if (!empty($monthlyRecords)): ?>


                <?php foreach ($monthlyRecords as $monthKey => $records): ?>


                    <?php

                    $parts = explode('-', $monthKey);

                    $year = $parts[0];

                    $monthNumber = intval($parts[1]);

                    $monthName = $monthNames[$monthNumber];

                    $recordTotal = count($records);

                    ?>


                    <!-- MONTH CARD -->

                    <div class="month-card">


                        <!-- MONTH HEADER -->

                        <div class="month-header">


                            <div class="month-title">

                                <i class="fa-regular fa-calendar"></i>

                                <span>

                                    <?= $monthName . " " . $year ?>

                                </span>

                            </div>


                            <div class="record-count">

                                <?= $recordTotal ?>

                                <?= ($recordTotal == 1)
                                    ? "Record"
                                    : "Records"
                                ?>

                            </div>


                        </div>



                        <!-- TABLE -->

                        <div class="table-wrapper">


                            <table class="record-table">


                                <thead>

                                    <tr>

                                        <th>
                                            No.
                                        </th>

                                        <th>
                                            Name
                                        </th>

                                        <th>
                                            Shift
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Qty Powder (kg)
                                        </th>

                                        <th>
                                            Qty Scrap (kg)
                                        </th>

                                        <th>
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php

                                    $no = 1;

                                    foreach ($records as $row):

                                    ?>

                                    <tr>


                                        <td>

                                            <?= $no++ ?>

                                        </td>


                                        <td class="name-cell">

                                            <?= htmlspecialchars(
                                                $row['name'] ?? ''
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $row['shift'] ?? ''
                                            ) ?>

                                        </td>


                                        <td>

                                            <?php

                                            if (!empty($row['date'])) {

                                                echo date(
                                                    'd/m/Y',
                                                    strtotime($row['date'])
                                                );

                                            }

                                            ?>

                                        </td>


                                        <td>

                                            <?= number_format(
                                                floatval(
                                                    $row['output_powder'] ?? 0
                                                ),
                                                2
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= number_format(
                                                floatval(
                                                    $row['qty_scarp'] ?? 0
                                                ),
                                                2
                                            ) ?>

                                        </td>


                                        <td class="action-cell">


                                            <a
                                                href="form.php?id=<?= intval($row['id']) ?>"
                                                class="edit-btn"
                                            >

                                                <i class="fa-solid fa-pen"></i>

                                                Edit

                                            </a>


                                            <a
                                                href="delete.php?id=<?= intval($row['id']) ?>"
                                                class="delete-btn"
                                                onclick="return confirm('Are you sure you want to delete this record?');"
                                            >

                                                <i class="fa-solid fa-trash"></i>

                                                Delete

                                            </a>


                                        </td>


                                    </tr>


                                    <?php endforeach; ?>


                                </tbody>


                            </table>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="month-card">


                    <div class="no-record">

                        <i class="fa-regular fa-folder-open"></i>

                        <p>
                            No operator records available.
                        </p>

                    </div>


                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>