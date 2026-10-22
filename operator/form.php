<?php

include "../db.php";

$message = "";


// ==================================================
// CHECK EDIT MODE
// ==================================================

$edit_mode = false;
$id = "";

$name = "";
$shift = "";
$date = date('Y-m-d');
$powder = "";
$scrap = "";


// ==================================================
// GET RECORD FOR EDIT
// ==================================================

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "SELECT * FROM operator WHERE id = $id";

    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        $edit_mode = true;

        $name = $row['name'] ?? "";
        $shift = $row['shift'] ?? "";
        $date = $row['date'] ?? date('Y-m-d');
        $powder = $row['output_powder'] ?? "";
        $scrap = $row['qty_scarp'] ?? "";

    } else {

        $message = "Record not found.";

    }
}


// ==================================================
// SAVE / UPDATE
// ==================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'] ?? "";
    $shift = $_POST['shift'] ?? "";
    $date = $_POST['date'] ?? "";
    $powder = $_POST['powder'] ?? "";
    $scrap = $_POST['scrap'] ?? "";

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;


    // ==================================================
    // UPDATE EXISTING RECORD
    // ==================================================

    if ($id > 0) {

        $sql = "UPDATE operator SET
                    name = '$name',
                    shift = '$shift',
                    date = '$date',
                    output_powder = '$powder',
                    qty_scarp = '$scrap'
                WHERE id = $id";


        if (mysqli_query($conn, $sql)) {

            header("Location: record.php");
            exit;

        } else {

            $message = "Error: " . mysqli_error($conn);

        }


    // ==================================================
    // SAVE NEW RECORD
    // ==================================================

    } else {

        $sql = "INSERT INTO operator
                (date, name, shift, output_powder, qty_scarp)
                VALUES
                ('$date', '$name', '$shift', '$powder', '$scrap')";


        if (mysqli_query($conn, $sql)) {

            $message = "Record saved successfully!";

            // Clear form after save
            $name = "";
            $shift = "";
            $date = date('Y-m-d');
            $powder = "";
            $scrap = "";

        } else {

            $message = "Error: " . mysqli_error($conn);

        }

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Waste Recycle Form</title>


<!-- ==================================================
     FONT AWESOME
================================================== -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* ========================================
   RESET
======================================== */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}


/* ========================================
   BODY
======================================== */

body {
    background: #f4f7f9;
}


/* ========================================
   MAIN CONTAINER
======================================== */

.container {
    display: flex;
    min-height: 100vh;
}


/* ========================================
   SIDEBAR
======================================== */

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


/* ========================================
   LOGO AREA
======================================== */

.logo-area {

    height: 145px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 15px;

    border-bottom: 1px solid rgba(255,255,255,0.18);
}


/* ========================================
   LOGO
======================================== */

.logo-area img {

    width: 72px;

    height: 72px;

    object-fit: contain;

    background: #fff;

    padding: 5px;

    border-radius: 0;

    display: block;
}


/* ========================================
   SIDEBAR TITLE
======================================== */

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


/* ========================================
   SIDEBAR LINE
======================================== */

.sidebar-line {

    height: 1px;

    background: rgba(255,255,255,0.18);

    margin: 18px 18px 20px;
}


/* ========================================
   SIDEBAR MENU
======================================== */

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


/* ========================================
   RIGHT SIDE
======================================== */

.right-side {

    flex: 1;

    min-width: 0;

    margin-left: 235px;

    display: flex;

    flex-direction: column;
}


/* ========================================
   TOP HEADER
======================================== */

.top-header {

    width: 100%;

    height: 70px;

    background: white;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    padding: 0 35px;

    border-bottom: 1px solid #ddd;

    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);

    flex-shrink: 0;
}


/* ========================================
   HEADER RIGHT
======================================== */

.header-right {

    display: flex;

    align-items: center;

    gap: 15px;
}


/* ========================================
   OPERATOR PROFILE
======================================== */

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


/* ========================================
   PROFILE ICON
======================================== */

.profile-icon {

    width: 35px;

    height: 35px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #e9eef0;

    font-size: 18px;
}


/* ========================================
   LOGOUT BUTTON
======================================== */

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

    font-size: 25px;

    transition: 0.2s;
}


.logout-btn:hover {

    background: #f2f2f2;
}


/* ========================================
   CONTENT
======================================== */

.content {

    flex: 1;

    padding: 45px 60px;
}


/* ========================================
   MAIN TITLE
======================================== */

.main-title {

    text-align: center;

    font-size: 30px;

    font-weight: bold;

    color: #333;

    margin-bottom: 40px;
}


/* ========================================
   MESSAGE
======================================== */

.message {

    text-align: center;

    margin-bottom: 25px;

    color: #008B8B;

    font-weight: bold;
}


/* ========================================
   FORM CONTAINER
======================================== */

.form-container {

    max-width: 750px;

    margin: 0 auto;

    background: white;

    padding: 40px 50px;

    border-radius: 12px;

    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
}


/* ========================================
   FORM ROW
======================================== */

.form-row {

    display: flex;

    align-items: center;

    margin-bottom: 20px;
}


/* ========================================
   LABEL
======================================== */

.form-row label {

    width: 180px;

    font-size: 16px;

    font-weight: bold;

    color: #333;
}


/* ========================================
   INPUT + SELECT
======================================== */

.form-row input,
.form-row select {

    flex: 1;

    padding: 12px 14px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 16px;
}


/* ========================================
   INPUT FOCUS
======================================== */

.form-row input:focus,
.form-row select:focus {

    outline: none;

    border-color: #008B8B;
}


/* ========================================
   SECTION TITLE
======================================== */

.section-title {

    font-size: 17px;

    font-weight: bold;

    color: #008B8B;

    margin-top: 25px;

    margin-bottom: 30px;

    padding-bottom: 8px;

    border-bottom: 2px solid #008B8B;
}


/* ========================================
   QUANTITY ROW
======================================== */

.date-qty-row {

    display: flex;

    gap: 20px;

    margin-bottom: 22px;
}


/* ========================================
   QUANTITY ITEM
======================================== */

.date-qty-item {

    flex: 1;
}


/* ========================================
   QUANTITY LABEL
======================================== */

.date-qty-item label {

    display: block;

    font-size: 15px;

    font-weight: bold;

    color: #333;

    margin-bottom: 7px;
}


/* ========================================
   QUANTITY INPUT
======================================== */

.date-qty-item input {

    width: 100%;

    padding: 12px 14px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 16px;
}


/* ========================================
   QUANTITY INPUT FOCUS
======================================== */

.date-qty-item input:focus {

    outline: none;

    border-color: #008B8B;
}


/* ========================================
   SAVE / UPDATE BUTTON
======================================== */

.save-area {

    text-align: center;

    margin-top: 30px;
}


.save-btn {

    background: #0d843b;

    color: white;

    border: none;

    padding: 13px 35px;

    border-radius: 7px;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}


.save-btn:hover {

    background: #006f06;
}


/* ========================================
   RESPONSIVE
======================================== */

@media (max-width: 900px) {

    .sidebar {

        width: 210px;

    }


    .right-side {

        margin-left: 210px;

    }

}


@media (max-width: 800px) {

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

        font-size: 17px;

    }


    .logout-btn {

        width: 42px;

        height: 42px;

        font-size: 22px;

    }


    .content {

        padding: 30px 20px;

    }


    .form-container {

        padding: 30px 25px;

    }


    .date-qty-row {

        flex-direction: column;

        gap: 10px;

    }


    .form-row {

        flex-direction: column;

        align-items: flex-start;

        gap: 8px;

    }


    .form-row label {

        width: auto;

    }


    .form-row input,
    .form-row select {

        width: 100%;

    }

}


@media (max-width: 700px) {

    .sidebar {

        width: 70px;

        padding: 0;

    }


    .logo-area {

        height: 100px;

        padding: 10px;

    }


    .logo-area img {

        width: 48px;

        height: 48px;

        margin-bottom: 0;

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


    .right-side {

        width: calc(100% - 70px);

    }


    .content {

        padding: 25px 15px;

    }


    .main-title {

        font-size: 22px;

        margin-bottom: 25px;

    }

}

</style>

</head>


<body>


<div class="container">


    <!-- ==================================================
         SIDEBAR
         SIDEBAR SAHAJA DIUBAH
    ================================================== -->

    <div class="sidebar">


        <!-- LOGO -->

        <div class="logo-area">

            <img
                src="../img/LogoNichias.jpg"
                alt="NICHIAS Logo"
            >

        </div>


        <!-- SIDEBAR TITLE -->

        <div class="sidebar-title">

            <h2>
                OPERATOR SYSTEM
            </h2>

            <p>
                Waste Recycle
            </p>

        </div>


        <!-- LINE -->

        <div class="sidebar-line"></div>


        <!-- MENU -->

        <div class="menu">


            <!-- FORM -->

            <a
                href="form.php"
                class="active"
            >

                <i class="fa-solid fa-file-pen"></i>

                <span>
                    Form
                </span>

            </a>


            <!-- RECORDS -->

            <a
                href="record.php"
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


        <!-- ==================================================
             TOP HEADER
        ================================================== -->

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



        <!-- ==================================================
             CONTENT
             FORM LAMA KEKAL
        ================================================== -->

        <div class="content">


            <!-- MAIN TITLE -->

            <div class="main-title">

                <?php if ($edit_mode): ?>

                    EDIT WASTE RECYCLE FORM

                <?php else: ?>

                    WASTE RECYCLE FORM

                <?php endif; ?>

            </div>



            <!-- MESSAGE -->

            <?php if ($message != ""): ?>

                <div class="message">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>



            <!-- FORM CONTAINER -->

            <div class="form-container">


                <form method="POST">


                    <!-- EDIT ID -->

                    <?php if ($edit_mode): ?>

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $id ?>"
                        >

                    <?php endif; ?>



                    <!-- NAME -->

                    <div class="form-row">

                        <label>
                            Name:
                        </label>


                        <input
                            type="text"
                            name="name"
                            placeholder="Enter name"
                            value="<?= htmlspecialchars($name) ?>"
                            required
                        >

                    </div>



                    <!-- SHIFT -->

                    <div class="form-row">

                        <label>
                            Shift:
                        </label>


                        <select
                            name="shift"
                            required
                        >

                            <option
                                value=""
                                disabled
                                <?= $shift == "" ? "selected" : "" ?>
                            >
                                Select Shift
                            </option>


                            <option
                                value="Normal"
                                <?= $shift == "Normal" ? "selected" : "" ?>
                            >
                                Normal
                            </option>


                            <option
                                value="Morning"
                                <?= $shift == "Morning" ? "selected" : "" ?>
                            >
                                Morning
                            </option>


                            <option
                                value="Afternoon"
                                <?= $shift == "Afternoon" ? "selected" : "" ?>
                            >
                                Afternoon
                            </option>


                            <option
                                value="Night"
                                <?= $shift == "Night" ? "selected" : "" ?>
                            >
                                Night
                            </option>

                        </select>

                    </div>



                    <!-- DATE -->

                    <div class="form-row">

                        <label>
                            Date:
                        </label>


                        <input
                            type="date"
                            name="date"
                            value="<?= htmlspecialchars($date) ?>"
                            required
                        >

                    </div>



                    <!-- POWDER -->

                    <div class="section-title">

                        POWDER

                    </div>


                    <div class="date-qty-row">


                        <div class="date-qty-item">


                            <label>
                                Qty Powder (kg)
                            </label>


                            <input
                                type="number"
                                name="powder"
                                step="0.01"
                                min="0"
                                placeholder="Enter quantity"
                                value="<?= htmlspecialchars($powder) ?>"
                                required
                            >


                        </div>


                    </div>



                    <!-- SCRAP TOMBO -->

                    <div class="section-title">

                        SCRAP TOMBO

                    </div>


                    <div class="date-qty-row">


                        <div class="date-qty-item">


                            <label>
                                Qty Scrap (kg)
                            </label>


                            <input
                                type="number"
                                name="scrap"
                                step="0.01"
                                min="0"
                                placeholder="Enter quantity"
                                value="<?= htmlspecialchars($scrap) ?>"
                                required
                            >


                        </div>


                    </div>



                    <!-- SAVE / UPDATE -->

                    <div class="save-area">


                        <?php if ($edit_mode): ?>

                            <button
                                type="submit"
                                class="save-btn"
                            >

                                UPDATE RECORD

                            </button>

                        <?php else: ?>

                            <button
                                type="submit"
                                class="save-btn"
                            >

                                SAVE RECORD

                            </button>

                        <?php endif; ?>


                    </div>


                </form>


            </div>


        </div>


    </div>


</div>


</body>

</html>