
<?php
session_start();
include "../db.php";

$message = "";

// ==================================================
// DEFAULT VALUES
// ==================================================

$edit_mode = false;
$id = 0;

$name = "";
$shift = "";
$date = date('Y-m-d');
$powder = "";
$scrap = "";

// ==================================================
// SUCCESS MESSAGE
// ==================================================

if (isset($_GET['saved'])) {
    $message = "Record saved successfully!";
}

if (isset($_GET['updated'])) {
    $message = "Record updated successfully!";
}

// ==================================================
// GET EXISTING RECORD FOR EDIT
// URL: form.php?id=1
// ==================================================

if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    if ($id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, shift, date, output_powder, qty_scarp
             FROM operator
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {

            $edit_mode = true;

            $id = (int) $row['id'];
            $name = $row['name'] ?? "";
            $shift = $row['shift'] ?? "";
            $date = $row['date'] ?? date('Y-m-d');
            $powder = $row['output_powder'] ?? "";
            $scrap = $row['qty_scarp'] ?? "";

        } else {

            $message = "Record not found.";
            $id = 0;

        }

        mysqli_stmt_close($stmt);
    }
}

// ==================================================
// SAVE / UPDATE RECORD
// ==================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int) ($_POST['id'] ?? 0);

    $name = trim($_POST['name'] ?? "");
    $shift = trim($_POST['shift'] ?? "");
    $date = $_POST['date'] ?? "";
    $powder = $_POST['powder'] ?? "";
    $scrap = $_POST['scrap'] ?? "";

    if (
        $name === "" ||
        $shift === "" ||
        $date === "" ||
        $powder === "" ||
        $scrap === ""
    ) {

        $message = "Please fill in all fields.";
        $edit_mode = ($id > 0);

    } elseif (
        !in_array($shift, ['Normal', 'Morning', 'Afternoon', 'Night'], true)
    ) {

        $message = "Invalid shift selected.";
        $edit_mode = ($id > 0);

    } elseif (
        !is_numeric($powder) ||
        !is_numeric($scrap) ||
        (float) $powder < 0 ||
        (float) $scrap < 0
    ) {

        $message = "Please enter valid quantities.";
        $edit_mode = ($id > 0);

    } else {

        // ------------------------------------------
        // UPDATE EXISTING RECORD
        // ------------------------------------------

        if ($id > 0) {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE operator
                 SET name = ?,
                     shift = ?,
                     date = ?,
                     output_powder = ?,
                     qty_scarp = ?
                 WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssssi",
                $name,
                $shift,
                $date,
                $powder,
                $scrap,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: record.php?updated=1");
                exit;

            } else {

                $message = "Update failed. Please try again.";
                $edit_mode = true;

                mysqli_stmt_close($stmt);
            }

        } else {

            // --------------------------------------
            // SAVE NEW RECORD
            // --------------------------------------

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO operator
                 (date, name, shift, output_powder, qty_scarp)
                 VALUES (?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $date,
                $name,
                $shift,
                $powder,
                $scrap
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: form.php?saved=1");
                exit;

            } else {

                $message = "Save failed. Please try again.";
                mysqli_stmt_close($stmt);
            }
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

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f7f9;
}

.container {
    display: flex;
    min-height: 100vh;
}

.sidebar {
    width: 235px;
    min-height: 100vh;
    background: #1e1768;
    color: white;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
}

.logo-area {
    height: 145px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.18);
}

.logo-area img {
    width: 72px;
    height: 72px;
    object-fit: contain;
    background: white;
    padding: 5px;
}

.sidebar-title {
    text-align: center;
    padding: 18px 10px 5px;
}

.sidebar-title h2 {
    font-size: 12px;
    letter-spacing: 0.6px;
    line-height: 1.4;
}

.sidebar-title p {
    font-size: 10px;
    color: rgba(255,255,255,0.7);
    margin-top: 3px;
}

.sidebar-line {
    height: 1px;
    background: rgba(255,255,255,0.18);
    margin: 18px 18px 20px;
}

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
    padding: 12px 14px;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    border-radius: 7px;
    font-size: 13.5px;
    font-weight: 600;
}

.menu a i {
    width: 20px;
    text-align: center;
}

.menu a:hover,
.menu a.active {
    background: rgba(255,255,255,0.18);
    color: white;
}

.right-side {
    flex: 1;
    min-width: 0;
    margin-left: 235px;
    display: flex;
    flex-direction: column;
}

.top-header {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 35px;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

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
}

.profile-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e9eef0;
}

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
    font-size: 23px;
}

.logout-btn:hover {
    background: #f2f2f2;
}

.content {
    flex: 1;
    padding: 45px 60px;
}

.main-title {
    text-align: center;
    font-size: 30px;
    font-weight: bold;
    color: #333;
    margin-bottom: 35px;
}

.message {
    text-align: center;
    margin: 0 auto 25px;
    color: #008B8B;
    font-weight: bold;
}

.form-container {
    max-width: 750px;
    margin: 0 auto;
    background: white;
    padding: 40px 50px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.form-row label {
    width: 180px;
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.form-row input,
.form-row select {
    flex: 1;
    min-width: 0;
    padding: 12px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}

.form-row input:focus,
.form-row select:focus,
.date-qty-item input:focus {
    outline: none;
    border-color: #008B8B;
}

.section-title {
    font-size: 17px;
    font-weight: bold;
    color: #008B8B;
    margin-top: 25px;
    margin-bottom: 25px;
    padding-bottom: 8px;
    border-bottom: 2px solid #008B8B;
}

.date-qty-row {
    display: flex;
    gap: 20px;
    margin-bottom: 22px;
}

.date-qty-item {
    flex: 1;
}

.date-qty-item label {
    display: block;
    font-size: 15px;
    font-weight: bold;
    color: #333;
    margin-bottom: 7px;
}

.date-qty-item input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
}

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

@media (max-width: 900px) {
    .sidebar {
        width: 210px;
    }

    .right-side {
        margin-left: 210px;
    }

    .content {
        padding: 30px 20px;
    }
}

@media (max-width: 700px) {
    .sidebar {
        width: 70px;
    }

    .logo-area {
        height: 100px;
        padding: 10px;
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
        padding: 15px 8px;
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
        margin-left: 70px;
        width: calc(100% - 70px);
    }

    .top-header {
        height: 65px;
        padding: 0 12px;
    }

    .operator-profile {
        font-size: 12px;
        padding-right: 10px;
    }

    .profile-icon {
        width: 30px;
        height: 30px;
    }

    .logout-btn {
        width: 38px;
        height: 38px;
        font-size: 19px;
    }

    .content {
        padding: 25px 12px;
    }

    .main-title {
        font-size: 22px;
        margin-bottom: 25px;
    }

    .form-container {
        padding: 25px 18px;
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
</style>
</head>

<body>
<div class="container">

    <div class="sidebar">
        <div class="logo-area">
            <img src="../img/LogoNichias.jpg" alt="NICHIAS Logo">
        </div>

        <div class="sidebar-title">
            <h2>OPERATOR SYSTEM</h2>
            <p>Waste Recycle</p>
        </div>

        <div class="sidebar-line"></div>

        <div class="menu">
            <a href="form.php" class="<?= !$edit_mode ? 'active' : '' ?>">
                <i class="fa-solid fa-file-pen"></i>
                <span>Form</span>
            </a>

            <a href="record.php">
                <i class="fa-solid fa-folder-open"></i>
                <span>Records</span>
            </a>
        </div>
    </div>

    <div class="right-side">

        <div class="top-header">
            <div class="header-right">
                <div class="operator-profile">
                    <span class="profile-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <span>OPERATOR</span>
                </div>

                <a href="../login.php" class="logout-btn" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>

        <div class="content">

            <div class="main-title">
                <?= $edit_mode ? 'EDIT WASTE RECYCLE FORM' : 'WASTE RECYCLE FORM' ?>
            </div>

            <?php if ($message !== ""): ?>
                <div class="message">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="form-container">

                <form method="POST" action="form.php<?= $edit_mode ? '?id=' . (int)$id : '' ?>">

                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="id" value="<?= (int)$id ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <label>Name:</label>
                        <input
                            type="text"
                            name="name"
                            placeholder="Enter name"
                            value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="form-row">
                        <label>Shift:</label>
                        <select name="shift" required>
                            <option value="" disabled <?= $shift === '' ? 'selected' : '' ?>>
                                Select Shift
                            </option>
                            <option value="Normal" <?= $shift === 'Normal' ? 'selected' : '' ?>>
                                Normal
                            </option>
                            <option value="Morning" <?= $shift === 'Morning' ? 'selected' : '' ?>>
                                Morning
                            </option>
                            <option value="Afternoon" <?= $shift === 'Afternoon' ? 'selected' : '' ?>>
                                Afternoon
                            </option>
                            <option value="Night" <?= $shift === 'Night' ? 'selected' : '' ?>>
                                Night
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Date:</label>
                        <input
                            type="date"
                            name="date"
                            value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="section-title">POWDER</div>

                    <div class="date-qty-row">
                        <div class="date-qty-item">
                            <label>Qty Powder (kg)</label>
                            <input
                                type="number"
                                name="powder"
                                step="0.01"
                                min="0"
                                placeholder="Enter quantity"
                                value="<?= htmlspecialchars((string)$powder, ENT_QUOTES, 'UTF-8') ?>"
                                required>
                        </div>
                    </div>

                    <div class="section-title">SCRAP TOMBO</div>

                    <div class="date-qty-row">
                        <div class="date-qty-item">
                            <label>Qty Scrap (kg)</label>
                            <input
                                type="number"
                                name="scrap"
                                step="0.01"
                                min="0"
                                placeholder="Enter quantity"
                                value="<?= htmlspecialchars((string)$scrap, ENT_QUOTES, 'UTF-8') ?>"
                                required>
                        </div>
                    </div>

                    <div class="save-area">
                        <button type="submit" class="save-btn">
                            <?= $edit_mode ? 'UPDATE RECORD' : 'SAVE RECORD' ?>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
</body>
</html>
