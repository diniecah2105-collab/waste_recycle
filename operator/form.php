
<?php
session_start();
require_once __DIR__ . "/../db.php";

$message = "";
$error = "";

$id = "";
$date = date('Y-m-d');
$name = "";
$shift = "";
$powder = "";
$scrap = "";
$edit_mode = false;

// ======================================
// GET RECORD FOR EDIT
// ======================================
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM operator WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $edit_mode = true;
                $date = $row['date'];
                $name = $row['name'];
                $shift = $row['shift'];
                $powder = $row['output_powder'];
                $scrap = $row['qty_scarp'];
            } else {
                $error = "Record not found.";
                $id = "";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Unable to retrieve record.";
        }
    } else {
        $error = "Invalid record ID.";
        $id = "";
    }
}

// ======================================
// SAVE OR UPDATE RECORD
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $posted_id = filter_var(
        $_POST['id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $id = ($posted_id && $posted_id > 0) ? $posted_id : "";
    $edit_mode = ($id !== "");

    $date = trim($_POST['date'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $shift = trim($_POST['shift'] ?? '');
    $powder = trim($_POST['output_powder'] ?? '');
    $scrap = trim($_POST['qty_scarp'] ?? '');

    if (
        $date === '' ||
        $name === '' ||
        $shift === '' ||
        $powder === '' ||
        $scrap === ''
    ) {
        $error = "Please fill in all fields.";

    } elseif (
        !in_array($shift, ['Day', 'Night'], true) ||
        !is_numeric($powder) ||
        !is_numeric($scrap) ||
        (float)$powder < 0 ||
        (float)$scrap < 0
    ) {
        $error = "Please enter valid shift, powder and scrap values.";

    } else {
        $powder = (float)$powder;
        $scrap = (float)$scrap;

        if ($edit_mode) {
            $sql = "UPDATE operator
                    SET date = ?, name = ?, shift = ?,
                        output_powder = ?, qty_scarp = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssddi",
                    $date,
                    $name,
                    $shift,
                    $powder,
                    $scrap,
                    $id
                );
            }

        } else {
            $sql = "INSERT INTO operator
                    (date, name, shift, output_powder, qty_scarp)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssdd",
                    $date,
                    $name,
                    $shift,
                    $powder,
                    $scrap
                );
            }
        }

        if (isset($stmt) && $stmt) {
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header("Location: form.php?success=1");
                exit;
            } else {
                $error = "Unable to save record. Please try again.";
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Unable to prepare database query.";
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Record saved successfully!";
}

function e($value) {
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Operator Form - Waste Recycle System</title>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f6fb;
    color: #222;
}

/* ================= SIDEBAR ================= */
.sidebar {
    width: 235px;
    height: 100vh;
    background: #1e1768;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
    z-index: 10;
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
    width: 76px;
    height: 76px;
    object-fit: contain;
    background: white;
    padding: 5px;
}

.sidebar-title {
    text-align: center;
    padding: 20px 10px 5px;
}

.sidebar-title h2 {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.6px;
}

.sidebar-title p {
    font-size: 11px;
    color: rgba(255,255,255,0.75);
    margin-top: 5px;
}

.sidebar-line {
    height: 1px;
    background: rgba(255,255,255,0.18);
    margin: 22px 18px 20px;
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
    width: 100%;
    padding: 13px 14px;
    color: rgba(255,255,255,0.92);
    text-decoration: none;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.menu a i {
    width: 20px;
    text-align: center;
    font-size: 16px;
}

.menu a:hover {
    background: rgba(255,255,255,0.10);
}

.menu a.active {
    background: rgba(255,255,255,0.18);
    color: #fff;
}

/* ================= RIGHT SIDE ================= */
.right-side {
    min-height: 100vh;
    margin-left: 235px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

/* HEADER - matching Records page style */
.top-header {
    width: 100%;
    height: 74px;
    min-height: 74px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 30px;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
    font-size: 14px;
    font-weight: bold;
    color: #222;
}

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
    color: #1e1768;
    font-size: 18px;
    transition: 0.2s;
}

.logout-btn:hover {
    background: #f2f2f2;
}

/* ================= MAIN CONTENT ================= */
.main {
    flex: 1;
    width: 100%;
    padding: 32px;
    min-width: 0;
}

.page-title {
    font-size: 25px;
    color: #1e1768;
    margin-bottom: 8px;
    font-weight: 700;
}

.page-title i {
    margin-right: 7px;
}

.subtitle {
    font-size: 14px;
    color: #777;
    margin-bottom: 28px;
    line-height: 1.6;
}

/* ================= FORM CARD ================= */
.form-card {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
    width: 100%;
    max-width: 1000px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
    color: #222;
}

.form-group input,
.form-group select {
    width: 100%;
    min-width: 0;
    height: 46px;
    padding: 0 13px;
    border: 1px solid #d5d8e2;
    border-radius: 7px;
    font-family: inherit;
    font-size: 14px;
    background: #fff;
    color: #333;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #2f4da0;
    box-shadow: 0 0 0 3px rgba(47,77,160,0.10);
}

.actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 12px 20px;
    border: none;
    border-radius: 7px;
    text-decoration: none;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-save {
    background: #1e1768;
    color: #fff;
}

.btn-save:hover {
    background: #30268c;
}

.btn-reset {
    background: #e9ebf2;
    color: #333;
}

.btn-reset:hover {
    background: #dce0ea;
}

/* ================= ALERTS ================= */
.alert {
    padding: 14px 16px;
    border-radius: 7px;
    margin-bottom: 22px;
    font-size: 14px;
    line-height: 1.5;
}

.alert i {
    margin-right: 7px;
}

.alert.success {
    background: #e7f7ed;
    color: #176b37;
}

.alert.error {
    background: #fdeaea;
    color: #a12626;
}

/* ================= RESPONSIVE ================= */
@media (max-width: 900px) {
    .main {
        padding: 24px;
    }

    .form-card {
        padding: 24px;
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
        padding: 15px 7px;
    }

    .menu a {
        justify-content: center;
        padding: 13px 5px;
    }

    .menu a span {
        display: none;
    }

    .menu a i {
        width: auto;
        font-size: 18px;
    }

    .top-header {
        padding: 0 12px;
        height: 65px;
        min-height: 65px;
    }

    .operator-profile {
        gap: 7px;
        padding-right: 10px;
        font-size: 12px;
    }

    .profile-icon {
        width: 32px;
        height: 32px;
    }

    .logout-btn {
        width: 38px;
        height: 38px;
    }

    .main {
        padding: 20px 14px;
    }

    .page-title {
        font-size: 21px;
    }

    .form-card {
        padding: 20px 16px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar">

    <div class="logo-area">
        <img src="../img/LogoNichias.jpg" alt="NICHIAS Logo">
    </div>

    <div class="sidebar-title">
        <h2>OPERATOR SYSTEM</h2>
        <p>Waste Recycle</p>
    </div>

    <div class="sidebar-line"></div>

    <nav class="menu">
        <a href="form.php" class="active">
            <i class="fa-solid fa-file-pen"></i>
            <span>Form</span>
        </a>

        <a href="record.php">
            <i class="fa-solid fa-folder-open"></i>
            <span>Records</span>
        </a>
    </nav>

</aside>

<!-- ================= RIGHT SIDE ================= -->
<div class="right-side">

    <!-- HEADER -->
    <header class="top-header">
        <div class="header-right">

            <div class="operator-profile">
                <span class="profile-icon">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span>OPERATOR</span>
            </div>

            <a href="../login.php"
               class="logout-btn"
               title="Logout"
               aria-label="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>

        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main">

        <h1 class="page-title">
            <i class="fa-solid fa-users"></i>
            <?= $edit_mode ? 'Edit Operator Record' : 'Operator Form' ?>
        </h1>

        <p class="subtitle">
            Enter and manage daily operator production records.
        </p>

        <div class="form-card">

            <?php if ($message !== ''): ?>
                <div class="alert success">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= e($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST"
                  action="form.php<?= $edit_mode ? '?id=' . (int)$id : '' ?>">

                <input type="hidden"
                       name="id"
                       value="<?= $edit_mode ? (int)$id : '' ?>">

                <div class="form-grid">

                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date"
                               id="date"
                               name="date"
                               value="<?= e($date) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="name">Operator Name</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="<?= e($name) ?>"
                               placeholder="Enter operator name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="shift">Shift</label>
                        <select id="shift" name="shift" required>
                            <option value="">Select Shift</option>

                            <option value="Day"
                                <?= $shift === 'Day' ? 'selected' : '' ?>>
                                Day
                            </option>

                            <option value="Night"
                                <?= $shift === 'Night' ? 'selected' : '' ?>>
                                Night
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="output_powder">Output Powder</label>
                        <input type="number"
                               step="any"
                               min="0"
                               id="output_powder"
                               name="output_powder"
                               value="<?= e($powder) ?>"
                               placeholder="Enter output powder"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="qty_scarp">Qty Scrap</label>
                        <input type="number"
                               step="any"
                               min="0"
                               id="qty_scarp"
                               name="qty_scarp"
                               value="<?= e($scrap) ?>"
                               placeholder="Enter quantity scrap"
                               required>
                    </div>

                </div>

                <div class="actions">

                    <button type="submit" class="btn btn-save">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <?= $edit_mode ? 'Update Record' : 'Save Record' ?>
                    </button>

                    <a href="form.php" class="btn btn-reset">
                        <i class="fa-solid fa-rotate-left"></i>
                        Clear
                    </a>

                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>
