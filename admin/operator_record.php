<?php

include __DIR__ . "/../db.php";


// ========================================
// AMBIL SEMUA DATA OPERATOR
// ========================================

$sql = "
    SELECT
        id,
        name,
        shift,
        date,
        output_powder,
        qty_scarp
    FROM operator
    ORDER BY date DESC, id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}


// ========================================
// GROUP MENGIKUT BULAN
// ========================================

$monthly_data = [];

while ($row = mysqli_fetch_assoc($result)) {

    if (empty($row['date'])) {
        continue;
    }

    $timestamp = strtotime($row['date']);

    if ($timestamp === false) {
        continue;
    }

    $month_key = date('Y-m', $timestamp);

    $monthly_data[$month_key][] = $row;
}


// ========================================
// BULAN TERBARU DI ATAS
// ========================================

krsort($monthly_data);


// ========================================
// NAMA BULAN
// ========================================

$month_names = [

    1  => 'JANUARY',
    2  => 'FEBRUARY',
    3  => 'MARCH',
    4  => 'APRIL',
    5  => 'MAY',
    6  => 'JUNE',
    7  => 'JULY',
    8  => 'AUGUST',
    9  => 'SEPTEMBER',
    10 => 'OCTOBER',
    11 => 'NOVEMBER',
    12 => 'DECEMBER'

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

<title>Operator Records - Waste Recycle</title>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* ========================================
   RESET
======================================== */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* ========================================
   BODY
======================================== */

body {

    font-family: Arial, sans-serif;

    background: #f5f6fa;

    color: #222;

}


/* ========================================
   SIDEBAR
======================================== */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 235px;

    height: 100vh;

    background: #1e1768;

    color: white;

    padding-top: 25px;

}


/* ========================================
   LOGO
======================================== */

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

    background: rgba(255,255,255,.25);

    margin: 0 18px 18px;

}


/* ========================================
   MENU
======================================== */

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


/* ========================================
   LOGOUT
======================================== */

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


/* ========================================
   MAIN
======================================== */

.main {

    margin-left: 235px;

    padding: 30px;

}


/* ========================================
   TOP SECTION
======================================== */

.top-section {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;

}


.top-section h1 {

    color: #243b7a;

    font-size: 26px;

}


.top-actions {

    display: flex;

    align-items: center;

    gap: 10px;

}


/* ========================================
   SEARCH
======================================== */

.search-box {

    position: relative;

}


.search-box i {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #243b7a;

    font-size: 14px;

}


.search-box input {

    width: 220px;

    padding: 10px 12px 10px 36px;

    border: 1px solid #c7d4f5;

    border-radius: 6px;

    background: white;

    outline: none;

    font-size: 14px;

}


.search-box input:focus {

    border-color: #243b7a;

    box-shadow:
        0 0 0 2px
        rgba(36,59,122,.10);

}


/* ========================================
   MONTH SECTION
======================================== */

.month-section {

    margin-bottom: 12px;

}


/* ========================================
   MONTH HEADER
======================================== */

.month-header {

    width: 100%;

    background: white;

    border: none;

    border-radius: 8px;

    padding: 17px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    cursor: pointer;

    box-shadow:
        0 2px 8px rgba(0,0,0,.07);

    transition: .2s;

    text-align: left;

}


.month-header:hover {

    background: #f8faff;

}


/* ========================================
   MONTH LEFT
======================================== */

.month-left {

    display: flex;

    align-items: center;

    gap: 13px;

}


.month-icon {

    width: 40px;

    height: 40px;

    background: #eaf0ff;

    color: #243b7a;

    border-radius: 7px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

}


.month-name {

    text-align: left;

}


.month-name h2 {

    color: #243b7a;

    font-size: 18px;

}


.month-name p {

    color: #6b7280;

    font-size: 12px;

    margin-top: 4px;

}


/* ========================================
   MONTH ACTIONS
======================================== */

.month-actions {

    display: flex;

    align-items: center;

    gap: 14px;

}


/* ========================================
   MONTH EXPORT BUTTON
======================================== */

.month-export-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 9px 14px;

    background: #243b7a;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 13px;

    font-weight: bold;

    transition: .2s;

    white-space: nowrap;

}


.month-export-btn:hover {

    background: #1e1768;

    transform: translateY(-1px);

}


.month-export-btn i {

    font-size: 14px;

}


/* ========================================
   MONTH ARROW
======================================== */

.month-arrow {

    color: #243b7a;

    font-size: 16px;

    transition: transform .25s;

}


.month-section.open .month-arrow {

    transform: rotate(180deg);

}


.month-section.open .month-header {

    border-radius: 8px 8px 0 0;

}


/* ========================================
   MONTH CONTENT
======================================== */

.month-content {

    display: none;

    background: white;

    border-radius: 0 0 8px 8px;

    padding: 20px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.07);

    overflow-x: auto;

}


.month-section.open .month-content {

    display: block;

}


/* ========================================
   TABLE
======================================== */

table {

    width: 100%;

    border-collapse: collapse;

    background: white;

}


/* ========================================
   TABLE HEADER
======================================== */

th {

    background: #080e46;

    color: #ffffff;

    padding: 13px 12px;

    font-size: 14px;

    font-weight: bold;

    border-bottom: 2px solid #d4d6dd;

    border-right: 1px solid #ededed;

}


th:last-child {

    border-right: none;

}


/* ========================================
   TABLE DATA
======================================== */

td {

    padding: 12px;

    border-bottom: 1px solid #e3e7ee;

    border-right: 1px solid #edf0f5;

    text-align: center;

    font-size: 14px;

    background: #ffffff;

    color: #222;

}


td:last-child {

    border-right: none;

}


/* ========================================
   ROW HOVER
======================================== */

tbody tr:not(.total-row):hover td {

    background: #f7f9ff;

}


/* ========================================
   ACTION
======================================== */

.action-cell {

    white-space: nowrap;

}


/* ========================================
   EDIT BUTTON
======================================== */

.edit-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    padding: 7px 11px;

    background: #0f0a58;

    color: #f7f7f7;

    border: 1px solid #0f0a58;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;

    margin-right: 5px;

    transition: .2s;

}


.edit-btn:hover {

    background: #dce6ff;

    border-color: #aebfe9;

}


/* ========================================
   DELETE BUTTON
======================================== */

.delete-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    padding: 7px 11px;

    background: #db0c1a;

    color: #f1e9eb;

    border: 1px solid #db0c1a;

    border-radius: 5px;

    text-decoration: none;

    font-size: 13px;

    transition: .2s;

}


.delete-btn:hover {

    background: #b80914;

    border-color: #b80914;

}


/* ========================================
   TOTAL ROW
======================================== */

.total-row td {

    background: #080e46;

    color: #ffffff;

    font-weight: bold;

    border-top: 2px solid #d2d2d4;

    border-bottom: none;

}


.total-label {

    text-align: left !important;

    color: #ffffff;

    font-size: 14px;

    padding-left: 12px;

}


.total-value {

    font-size: 15px;

    color: #ffffff;

}


/* ========================================
   EMPTY
======================================== */

.empty {

    background: white;

    padding: 40px;

    text-align: center;

    color: #777;

    border-radius: 8px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.08);

}


/* ========================================
   NO SEARCH
======================================== */

.no-search {

    display: none;

    background: white;

    padding: 30px;

    text-align: center;

    color: #777;

    border-radius: 8px;

    box-shadow:
        0 2px 8px rgba(0,0,0,.08);

}


.no-search i {

    font-size: 28px;

    margin-bottom: 10px;

    color: #243b7a;

}


/* ========================================
   RESPONSIVE
======================================== */

@media (max-width: 900px) {

    .top-section {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .top-actions {

        width: 100%;

    }


    .search-box {

        flex: 1;

        width: 100%;

    }


    .search-box input {

        width: 100%;

    }

}


@media (max-width: 800px) {

    .sidebar {

        width: 200px;

    }


    .main {

        margin-left: 200px;

    }

}


@media (max-width: 650px) {

    .month-header {

        padding: 14px;

    }


    .month-actions {

        gap: 8px;

    }


    .month-export-btn {

        padding: 8px 10px;

        font-size: 12px;

    }


    .month-export-btn i {

        font-size: 13px;

    }


    .month-arrow {

        font-size: 14px;

    }

}


@media (max-width: 600px) {

    .sidebar {

        width: 180px;

    }


    .main {

        margin-left: 180px;

        padding: 20px;

    }


    .top-actions {

        width: 100%;

        align-items: stretch;

    }

}

</style>

</head>


<body>


<!-- ========================================
     SIDEBAR
======================================== -->

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


        <!-- DASHBOARD -->

        <a href="dashboard.php">

            <span class="menu-icon">

                <i class="fa-solid fa-chart-pie"></i>

            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- ADMIN FORM -->

        <a href="admin_form.php">

            <span class="menu-icon">

                <i class="fa-solid fa-file-pen"></i>

            </span>

            <span>
                Admin Form
            </span>

        </a>


        <!-- ADMIN RECORDS -->

        <a href="admin_record.php">

            <span class="menu-icon">

                <i class="fa-solid fa-folder-open"></i>

            </span>

            <span>
                Admin Records
            </span>

        </a>


        <!-- OPERATOR RECORDS -->

        <a
            href="operator_record.php"
            class="active"
        >

            <span class="menu-icon">

                <i class="fa-solid fa-users-gear"></i>

            </span>

            <span>
                Operator Records
            </span>

        </a>


    </div>


    <!-- LOGOUT -->

    <div class="logout">

        <a href="../login.php">

            <span class="menu-icon">

                <i class="fa-solid fa-right-from-bracket"></i>

            </span>

            <span>
                Logout
            </span>

        </a>

    </div>


</div>



<!-- ========================================
     MAIN
======================================== -->

<div class="main">


    <!-- ========================================
         TOP SECTION
    ======================================== -->

    <div class="top-section">


        <h1>
            Operator Records
        </h1>


        <div class="top-actions">


            <!-- SEARCH MONTH -->

            <div class="search-box">

                <i
                    class="fa-solid fa-magnifying-glass"
                ></i>


                <input
                    type="text"
                    id="monthSearch"
                    placeholder="Search month..."
                    autocomplete="off"
                >

            </div>


        </div>


    </div>



    <!-- ========================================
         RECORDS
    ======================================== -->

    <?php if (empty($monthly_data)): ?>


        <!-- EMPTY -->

        <div class="empty">

            <i
                class="fa-solid fa-folder-open"
                style="
                    font-size:32px;
                    margin-bottom:12px;
                    color:#243b7a;
                "
            ></i>

            <br>

            No operator records found.

        </div>


    <?php else: ?>


        <div id="monthList">


        <?php

        $month_index = 0;

        ?>


        <?php foreach (
            $monthly_data
            as $month_key => $records
        ): ?>


            <?php

            // ========================================
            // TARIKH BULAN
            // ========================================

            $date_object =
                strtotime(
                    $month_key . '-01'
                );


            // ========================================
            // YEAR
            // ========================================

            $year =
                date(
                    'Y',
                    $date_object
                );


            // ========================================
            // MONTH NUMBER
            // ========================================

            $month_number =
                (int)date(
                    'n',
                    $date_object
                );


            // ========================================
            // MONTH NAME
            // ========================================

            $month_name =
                $month_names[
                    $month_number
                ];


            // ========================================
            // TOTAL MONTH
            // ========================================

            $month_powder = 0;

            $month_scrap = 0;


            foreach (
                $records
                as $record
            ) {

                $month_powder +=
                    (float)(
                        $record[
                            'output_powder'
                        ] ?? 0
                    );


                $month_scrap +=
                    (float)(
                        $record[
                            'qty_scarp'
                        ] ?? 0
                    );

            }


            // ========================================
            // BULAN TERBARU OPEN
            // ========================================

            $is_latest =
                ($month_index === 0);

            ?>


            <div
                class="
                    month-section
                    <?= $is_latest
                        ? 'open'
                        : ''
                    ?>
                "
                data-month="<?=

                    htmlspecialchars(

                        strtolower(

                            $month_name
                            . ' '
                            . $year

                        )

                    )

                ?>"
            >


                <!-- ====================================
                     MONTH HEADER
                ==================================== -->

                <button
                    type="button"
                    class="month-header"
                >


                    <!-- MONTH LEFT -->

                    <div class="month-left">


                        <!-- ICON -->

                        <div class="month-icon">

                            <i
                                class="
                                    fa-solid
                                    fa-calendar-days
                                "
                            ></i>

                        </div>


                        <!-- MONTH NAME -->

                        <div class="month-name">

                            <h2>

                                <?= htmlspecialchars(
                                    $month_name
                                ) ?>

                                <?= htmlspecialchars(
                                    $year
                                ) ?>

                            </h2>


                            <p>

                                <?= count($records) ?>

                                record(s)

                            </p>

                        </div>


                    </div>



                    <!-- =================================
                         MONTH ACTIONS
                    ================================== -->

                    <div class="month-actions">


                        <!-- EXPORT MONTH -->

                        <a
                            href="export_operator.php?month=<?= $month_number ?>&year=<?= $year ?>"
                            class="month-export-btn"
                            onclick="event.stopPropagation();"
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-file-excel
                                "
                            ></i>

                            Export Excel

                        </a>


                        <!-- ARROW -->

                        <div class="month-arrow">

                            <i
                                class="
                                    fa-solid
                                    fa-chevron-down
                                "
                            ></i>

                        </div>


                    </div>


                </button>



                <!-- ====================================
                     MONTH CONTENT
                ==================================== -->

                <div class="month-content">


                    <table>


                        <!-- TABLE HEADER -->

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


                        <!-- TABLE BODY -->

                        <tbody>


                        <?php

                        $no = 1;

                        foreach (
                            $records
                            as $row
                        ):

                        ?>


                            <tr>


                                <!-- NO -->

                                <td>

                                    <?= $no++ ?>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <?= htmlspecialchars(

                                        $row['name']
                                        ?? ''

                                    ) ?>

                                </td>


                                <!-- SHIFT -->

                                <td>

                                    <?= htmlspecialchars(

                                        $row['shift']
                                        ?? ''

                                    ) ?>

                                </td>


                                <!-- DATE -->

                                <td>

                                    <?= htmlspecialchars(

                                        $row['date']
                                        ?? ''

                                    ) ?>

                                </td>


                                <!-- POWDER -->

                                <td>

                                    <?= number_format(

                                        (float)(

                                            $row[
                                                'output_powder'
                                            ] ?? 0

                                        ),

                                        2

                                    ) ?>

                                </td>


                                <!-- SCRAP -->

                                <td>

                                    <?= number_format(

                                        (float)(

                                            $row[
                                                'qty_scarp'
                                            ] ?? 0

                                        ),

                                        2

                                    ) ?>

                                </td>


                                <!-- ACTION -->

                                <td class="action-cell">


                                    <!-- EDIT -->

                                    <a
                                        href="form.php?edit_id=<?= (int)$row['id'] ?>"
                                        class="edit-btn"
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-pen-to-square
                                            "
                                        ></i>

                                        Edit

                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="delete.php?id=<?= (int)$row['id'] ?>"
                                        class="delete-btn"
                                        onclick="
                                            return confirm(
                                                'Are you sure you want to delete this record?'
                                            );
                                        "
                                    >

                                        <i
                                            class="
                                                fa-solid
                                                fa-trash
                                            "
                                        ></i>

                                        Delete

                                    </a>


                                </td>


                            </tr>


                        <?php endforeach; ?>


                            <!-- =================================
                                 TOTAL
                            ================================== -->

                            <tr class="total-row">


                                <td
                                    colspan="4"
                                    class="total-label"
                                >

                                    TOTAL
                                    <?= htmlspecialchars(
                                        $month_name
                                    ) ?>

                                </td>


                                <!-- TOTAL POWDER -->

                                <td class="total-value">

                                    <?= number_format(
                                        $month_powder,
                                        2
                                    ) ?>

                                    kg

                                </td>


                                <!-- TOTAL SCRAP -->

                                <td class="total-value">

                                    <?= number_format(
                                        $month_scrap,
                                        2
                                    ) ?>

                                    kg

                                </td>


                                <td></td>


                            </tr>


                        </tbody>


                    </table>


                </div>


            </div>


            <?php

            $month_index++;

            ?>


        <?php endforeach; ?>


        </div>



        <!-- ========================================
             NO SEARCH
        ======================================== -->

        <div
            id="noSearch"
            class="no-search"
        >

            <i
                class="
                    fa-solid
                    fa-calendar-xmark
                "
            ></i>

            <br>

            No month found.

        </div>


    <?php endif; ?>


</div>



<script>


// ========================================
// ACCORDION
// ========================================

const monthHeaders =
    document.querySelectorAll(
        '.month-header'
    );


monthHeaders.forEach(
    function(header) {

        header.addEventListener(
            'click',
            function(event) {

                /*
                ========================================
                JIKA CLICK PADA EXPORT BUTTON
                JANGAN TOGGLE ACCORDION
                ========================================
                */

                if (
                    event.target.closest(
                        '.month-export-btn'
                    )
                ) {

                    return;

                }


                const section =
                    this.closest(
                        '.month-section'
                    );


                section.classList.toggle(
                    'open'
                );

            }
        );

    }
);



// ========================================
// SEARCH MONTH
// ========================================

const searchInput =
    document.getElementById(
        'monthSearch'
    );


if (searchInput) {

    searchInput.addEventListener(
        'input',
        function() {


            const keyword =
                this.value
                    .toLowerCase()
                    .trim();


            const sections =
                document.querySelectorAll(
                    '.month-section'
                );


            const noSearch =
                document.getElementById(
                    'noSearch'
                );


            let found = false;


            sections.forEach(
                function(section) {


                    const month =
                        section.dataset.month;


                    if (
                        month.includes(
                            keyword
                        )
                    ) {

                        section.style.display =
                            'block';

                        found = true;

                    } else {

                        section.style.display =
                            'none';

                    }

                }
            );


            if (noSearch) {

                noSearch.style.display =
                    found
                        ? 'none'
                        : 'block';

            }

        }
    );

}

</script>


</body>

</html>