
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

.sidebar {
    width: 235px;
    height: 100vh;
    background: #1e1768;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    padding: 25px 15px;
}

.logo {
    text-align: center;
    margin-bottom: 35px;
}

.logo img {
    width: 150px;
    max-width: 100%;
    background: white;
    padding: 8px;
    border-radius: 8px;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 14px 15px;
    margin-bottom: 8px;
    border-radius: 7px;
    font-size: 14px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #40369a;
}

.sidebar a i {
    width: 25px;
}

.main {
    margin-left: 235px;
    padding: 30px;
}

.page-title {
    color: #243b7a;
    font-size: 27px;
    margin-bottom: 8px;
}

.subtitle {
    color: #777;
    margin-bottom: 25px;
    font-size: 14px;
}

.form-card {
    background: white;
    max-width: 850px;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: bold;
    color: #333;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d5d9e4;
    border-radius: 7px;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #2f4da0;
}

.actions {
    display: flex;
    gap: 10px;
    margin-top: 5px;
}

.btn {
    border: none;
    padding: 12px 20px;
    border-radius: 7px;
    color: white;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
    display: inline-flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
}

.btn-save {
    background: #243b7a;
}

.btn-save:hover {
    background: #2f4da0;
}

.btn-reset {
    background: #6c757d;
}

.alert {
    padding: 13px;
    margin-bottom: 20px;
    border-radius: 7px;
    font-size: 14px;
}

.success {
    background: #d9f7e3;
    color: #176b35;
}

.error {
    background: #ffe5e5;
    color: #b42323;
}

@media(max-width: 800px) {
    .sidebar {
        width: 70px;
        padding: 20px 8px;
    }

    .logo img {
        width: 48px;
    }

    .sidebar a {
        text-align: center;
        padding: 14px 5px;
        font-size: 0;
    }

    .sidebar a i {
        font-size: 18px;
        width: auto;
    }

    .main {
        margin-left: 70px;
        padding: 18px;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }

    .form-card {
        padding: 20px;
    }
}
</style>
</head>

<body>

<div class="sidebar">
    <div class="logo">
        <img src="../img/LogoNichias.jpg" alt="Nichias Logo">
    </div>

    <a href="form.php" class="active">
        <i class="fa-solid fa-file-circle-plus"></i>
        Operator Form
    </a>

    <a href="record.php">
        <i class="fa-solid fa-folder-open"></i>
        Records
    </a>

    <a href="../login.php">
        <i class="fa-solid fa-right-from-bracket"></i>
        Back to Login
    </a>
</div>

<div class="main">

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
