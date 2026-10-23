
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

// GET RECORD FOR EDIT
if (isset($_GET['id'])) {
    $edit_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($edit_id && $edit_id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM operator WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $edit_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                $id = (int)$row['id'];
                $edit_mode = true;
                $date = $row['date'];
                $name = $row['name'];
                $shift = $row['shift'];
                $powder = $row['output_powder'];
                $scrap = $row['qty_scarp'];
            } else {
                $error = "Record not found.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = "Unable to retrieve record.";
        }
    } else {
        $error = "Invalid record ID.";
    }
}

// SAVE OR UPDATE
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
        !in_array($shift, ['Normal', 'Morning', 'Afternoon', 'Night'], true) ||
        !is_numeric($powder) ||
        !is_numeric($scrap) ||
        (float)$powder < 0 ||
        (float)$scrap < 0
    ) {
        $error = "Please enter valid shift, powder and scrap values.";

    } else {
        $powder = (float)$powder;
        $scrap = (float)$scrap;
        $stmt = false;

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

        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: form.php?success=1");
                exit;
            }

            $error = "Unable to save record. Please try again.";
            mysqli_stmt_close($stmt);
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

<title>Waste Recycle Form</title>

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
    background: #f4f6f8;
    color: #333;
}

/* SIDEBAR */
.sidebar {
    width: 235px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #1e1768;
    color: white;
    overflow-y: auto;
    z-index: 10;
}

.logo-area {
    height: 145px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid rgba(255,255,255,.2);
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
    letter-spacing: .5px;
}

.sidebar-title p {
    font-size: 11px;
    margin-top: 5px;
    color: rgba(255,255,255,.8);
}

.sidebar-line {
    height: 1px;
    background: rgba(255,255,255,.2);
    margin: 22px 18px;
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
    padding: 13px 14px;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.menu a i {
    width: 20px;
    text-align: center;
}

.menu a:hover,
.menu a.active {
    background: rgba(255,255,255,.2);
}

/* RIGHT SIDE */
.right-side {
    min-height: 100vh;
    margin-left: 235px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

/* TOP HEADER */
.top-header {
    height: 70px;
    min-height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 30px;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.operator-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 5px 16px 5px 5px;
    border: 1px solid #ddd;
    border-radius: 30px;
    background: white;
    font-size: 13px;
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
    color: #1e1768;
}

.logout-btn {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    color: #1e1768;
    text-decoration: none;
    font-size: 17px;
}

.logout-btn:hover {
    background: #f2f2f2;
}

/* MAIN CONTENT */
.main {
    width: 100%;
    padding: 26px 24px 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.page-title {
    width: 100%;
    text-align: center;
    font-size: 25px;
    color: #555;
    margin-bottom: 25px;
    font-weight: 700;
}

.page-title i {
    display: none;
}

.subtitle {
    display: none;
}

/* FORM CARD */
.form-card {
    width: 100%;
    max-width: 650px;
    background: white;
    padding: 28px 30px;
    border-radius: 10px;
    box-shadow: 0 3px 15px rgba(0,0,0,.07);
}

.form-grid {
    display: flex;
    flex-direction: column;
    gap: 17px;
}

.form-group {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 13px;
    font-weight: 500;
    color: #444;
}

.form-group input,
.form-group select {
    width: 100%;
    height: 39px;
    padding: 0 12px;
    border: 1px solid #d4d7dc;
    border-radius: 5px;
    background: white;
    color: #444;
    font-family: Arial, sans-serif;
    font-size: 13px;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #1e1768;
    box-shadow: 0 0 0 2px rgba(55,135,109,.1);
}

/* POWDER SECTION */
.form-group:nth-child(4) {
    margin-top: 3px;
}

.form-group:nth-child(4)::before {
    content: "POWDER";
    display: block;
    padding-bottom: 8px;
    border-bottom: 1px solid #1e1768;
    color: #1e1768;
    font-size: 13px;
    font-weight: bold;
}

/* SCRAP SECTION */
.form-group:nth-child(5) {
    margin-top: 3px;
}

.form-group:nth-child(5)::before {
    content: "SCRAP TOMBO";
    display: block;
    padding-bottom: 8px;
    border-bottom: 1px solid #1e1768;
    color: #1e1768;
    font-size: 13px;
    font-weight: bold;
}

/* BUTTONS */
.actions {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 25px;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 40px;
    padding: 11px 22px;
    border: none;
    border-radius: 5px;
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: .2s;
}

.btn-save {
    background: #37876d;
    color: white;
}

.btn-save:hover {
    background: #2d705a;
}

.btn-reset {
    background: #e9ebf2;
    color: #333;
}

.btn-reset:hover {
    background: #dce0ea;
}

/* ALERTS */
.alert {
    width: 100%;
    padding: 12px 14px;
    border-radius: 6px;
    margin-bottom: 18px;
    font-size: 13px;
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

/* RESPONSIVE */
@media (max-width: 900px) {
    .main {
        padding: 24px 18px;
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
        padding: 0 10px;
    }

    .operator-profile {
        font-size: 11px;
        padding-right: 9px;
        gap: 6px;
    }

    .profile-icon {
        width: 32px;
        height: 32px;
    }

    .logout-btn {
        width: 36px;
        height: 36px;
    }

    .main {
        padding: 22px 12px;
    }

    .page-title {
        font-size: 19px;
        margin-bottom: 20px;
    }

    .form-card {
        padding: 22px 16px;
    }
}
</style>
</head>

<body>


<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo-area">
        <img
            src="../img/LogoNichias.jpg"
            alt="NICHIAS Logo"
        >
    </div>

    <div class="sidebar-title">
        <h2>OPERATOR SYSTEM</h2>
        <p>Waste Recycle</p>
    </div>

    <div class="sidebar-line"></div>

    <div class="menu">

        <a href="form.php" class="active">
            <i class="fa-solid fa-file-pen"></i>
            <span>Form</span>
        </a>

        <a href="record.php">
            <i class="fa-solid fa-folder-open"></i>
            <span>Records</span>
        </a>

    </div>

</div>


<!-- RIGHT SIDE -->
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

    <!-- FORM CONTENT -->
    <main class="main">

        <h1 class="page-title">
            <?= $edit_mode ? 'EDIT OPERATOR RECORD' : 'WASTE RECYCLE FORM' ?>
        </h1>

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
                        <label for="name">Name:</label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="<?= e($name) ?>"
                               placeholder="Enter name"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="shift">Shift:</label>
                        <select id="shift" name="shift" required>
                            <option value="">Select Shift</option>
                            <option value="Normal"
                                <?= $shift === 'Normal' ? 'selected' : '' ?>>
                                Normal
                            </option>
                            <option value="Morning"
                                <?= $shift === 'Morning' ? 'selected' : '' ?>>
                                Morning
                            </option>
                            <option value="Afternoon"
                                <?= $shift === 'Afternoon' ? 'selected' : '' ?>>
                                Afternoon
                            </option>
                            <option value="Night"
                                <?= $shift === 'Night' ? 'selected' : '' ?>>
                                Night
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">Date:</label>
                        <input type="date"
                               id="date"
                               name="date"
                               value="<?= e($date) ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="output_powder">Qty Powder (kg)</label>
                        <input type="number"
                               step="any"
                               min="0"
                               id="output_powder"
                               name="output_powder"
                               value="<?= e($powder) ?>"
                               placeholder="Enter quantity"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="qty_scarp">Qty Scrap (kg)</label>
                        <input type="number"
                               step="any"
                               min="0"
                               id="qty_scarp"
                               name="qty_scarp"
                               value="<?= e($scrap) ?>"
                               placeholder="Enter quantity"
                               required>
                    </div>

                </div>

                <div class="actions">

                    <button type="submit" class="btn btn-save">
                        <?= $edit_mode ? 'UPDATE RECORD' : 'SAVE RECORD' ?>
                    </button>

                    <a href="form.php" class="btn btn-reset">
                        CLEAR
                    </a>

                </div>

            </form>
        </div>

    </main>

</div>

</body>
</html>
