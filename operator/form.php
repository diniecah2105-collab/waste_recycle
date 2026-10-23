
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
    }
}

// ======================================
// SAVE OR UPDATE RECORD
// ======================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = filter_var(
        $_POST['id'] ?? '',
        FILTER_VALIDATE_INT
    );

    $date = trim($_POST['date'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $shift = trim($_POST['shift'] ?? '');
    $powder = trim($_POST['output_powder'] ?? '');
    $scrap = trim($_POST['qty_scarp'] ?? '');

    $edit_mode = ($id !== false && $id > 0);

    if (
        $date === '' ||
        $name === '' ||
        $shift === '' ||
        $powder === '' ||
        $scrap === ''
    ) {
        $error = "Please fill in all fields.";
    } elseif (!is_numeric($powder) || !is_numeric($scrap)) {
        $error = "Powder and Scrap must be numbers.";
    } else {

        if ($edit_mode) {
            $sql = "UPDATE operator
                    SET date = ?, name = ?, shift = ?,
                        output_powder = ?, qty_scarp = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssssdi",
                $date,
                $name,
                $shift,
                $powder,
                $scrap,
                $id
            );

        } else {
            $sql = "INSERT INTO operator
                    (date, name, shift, output_powder, qty_scarp)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "ssssd",
                $date,
                $name,
                $shift,
                $powder,
                $scrap
            );
        }

        if ($stmt && mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);

            header("Location: form.php?success=1");
            exit;
        } else {
            $error = "Unable to save record. Please try again.";

            if ($stmt) {
                mysqli_stmt_close($stmt);
            }
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

/* SIDEBAR */
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

/* LOGO AREA */
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
    background: #fff;
    padding: 5px;
    display: block;
}

/* SIDEBAR TITLE */
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

.sidebar-line {
    height: 1px;
    background: rgba(255,255,255,0.18);
    margin: 18px 18px 20px;
}

/* MENU */
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

/* RIGHT SIDE */
.right-side {
    flex: 1;
    min-width: 0;
    margin-left: 235px;
    display: flex;
    flex-direction: column;
}

.top-header {
    height: 70px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 0 30px;
    border-bottom: 1px solid #e5e7eb;
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
    font-size: 13px;
    font-weight: 700;
}

.profile-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #e8eaf6;
    color: #1e1768;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logout-btn {
    color: #1e1768;
    font-size: 18px;
    text-decoration: none;
    padding: 8px;
}

.main {
    padding: 30px;
    width: 100%;
}

.page-title {
    font-size: 25px;
    color: #1e1768;
    margin-bottom: 8px;
}

.subtitle {
    font-size: 14px;
    color: #777;
    margin-bottom: 25px;
}

.form-card {
    background: #fff;
    padding: 28px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.06);
    max-width: 1000px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.form-group label {
    font-size: 14px;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d5d8e2;
    border-radius: 7px;
    font-size: 14px;
    background: #fff;
}

.actions {
    display: flex;
    gap: 12px;
    margin-top: 28px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 7px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.btn-save {
    background: #1e1768;
    color: white;
}

.btn-reset {
    background: #e9ebf2;
    color: #333;
}

.alert {
    padding: 13px 16px;
    border-radius: 7px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert.success {
    background: #e7f7ed;
    color: #176b37;
}

.alert.error {
    background: #fdeaea;
    color: #a12626;
}

@media (max-width: 700px) {
    .main {
        padding: 18px;
    }

    .form-card {
        padding: 18px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .top-header {
        padding: 0 15px;
    }
}
/* RESPONSIVE */
@media (max-width: 900px) {
    .sidebar {
        width: 210px;
    }

    .right-side {
        margin-left: 210px;
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



</div><!-- Penutup sidebar -->


    <!-- TOP HEADER: kekalkan kod header awak -->
    <div class="top-header">
        <div class="header-right">
            <div class="operator-profile">
                <span class="profile-icon">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span>OPERATOR</span>
            </div>

            <a href="../login.php"
               class="logout-btn"
               title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- CONTENT FORM -->
    <div class="main">

        <!-- Kekalkan page-title, subtitle,
             form-card dan keseluruhan form asal di sini -->

    </div>

</div><!-- Penutup right-side -->

</body>
</html>


        <!-- KEKALKAN SEMUA KOD FORM ASAL DI SINI -->

    </div>


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

        <form method="POST" action="form.php<?= $edit_mode ? '?id=' . (int)$id : '' ?>">

            <input type="hidden" name="id"
                   value="<?= $edit_mode ? (int)$id : '' ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date"
                           value="<?= e($date) ?>" required>
                </div>

                <div class="form-group">
                    <label for="name">Operator Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= e($name) ?>"
                           placeholder="Enter operator name" required>
                </div>

                <div class="form-group">
                    <label for="shift">Shift</label>
                    <select id="shift" name="shift" required>
                        <option value="">Select Shift</option>
                        <option value="Day" <?= $shift === 'Day' ? 'selected' : '' ?>>
                            Day
                        </option>
                        <option value="Night" <?= $shift === 'Night' ? 'selected' : '' ?>>
                            Night
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="output_powder">Output Powder</label>
                    <input type="number" step="any" min="0"
                           id="output_powder" name="output_powder"
                           value="<?= e($powder) ?>"
                           placeholder="Enter output powder" required>
                </div>

                <div class="form-group">
                    <label for="qty_scarp">Qty Scrap</label>
                    <input type="number" step="any" min="0"
                           id="qty_scarp" name="qty_scarp"
                           value="<?= e($scrap) ?>"
                           placeholder="Enter quantity scrap" required>
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
</div>

</body>
</html>
