<?php
session_start();
require_once __DIR__ . "/../db.php";

// ============================================
// CHECK ADMIN LOGIN
// ============================================
if (
    !isset($_SESSION['admin_login']) ||
    $_SESSION['admin_login'] !== true
) {
    header("Location: ../login.php");
    exit;
}

// ============================================
// DELETE RECORD - DALAM FILE YANG SAMA
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM operator WHERE id = ?");

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: operator_record.php");
    exit;
}

// ============================================
// GET OPERATOR RECORDS
// ============================================
$sql = "SELECT id, date, name, shift, output_powder, qty_scarp
        FROM operator
        ORDER BY date DESC, id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database error: " . htmlspecialchars(mysqli_error($conn)));
}

// Group records by month
$recordsByMonth = [];

while ($row = mysqli_fetch_assoc($result)) {
    $monthKey = date('Y-m', strtotime($row['date']));
    $monthLabel = date('F Y', strtotime($row['date']));

    if (!isset($recordsByMonth[$monthKey])) {
        $recordsByMonth[$monthKey] = [
            'label' => $monthLabel,
            'records' => []
        ];
    }

    $recordsByMonth[$monthKey]['records'][] = $row;
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Operator Records</title>

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
            height: auto;
            object-fit: contain;
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
            font-size: 27px;
            color: #243b7a;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .search-box {
            width: 100%;
            max-width: 400px;
            padding: 12px 15px;
            border: 1px solid #d5d9e4;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 22px;
            outline: none;
        }

        .search-box:focus {
            border-color: #2f4da0;
        }

        .month-card {
            background: white;
            border-radius: 10px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .month-header {
            width: 100%;
            border: none;
            background: #243b7a;
            color: white;
            padding: 17px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-align: left;
        }

        .month-header:hover {
            background: #2f4da0;
        }

        .month-header .month-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .count {
            background: white;
            color: #243b7a;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
        }

        .month-content {
            display: none;
            padding: 18px;
            overflow-x: auto;
        }

        .month-content.show {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th {
            background: #eef1fb;
            color: #243b7a;
            text-align: left;
            padding: 13px 12px;
            font-size: 13px;
            white-space: nowrap;
        }

        td {
            padding: 13px 12px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }

        tr:hover td {
            background: #fafbff;
        }

        .actions {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .btn {
            border: none;
            padding: 8px 11px;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            white-space: nowrap;
        }

        .btn-edit {
            background: #e69a18;
        }

        .btn-edit:hover {
            background: #c47e06;
        }

        .btn-delete {
            background: #dc3545;
        }

        .btn-delete:hover {
            background: #b92332;
        }

        .delete-form {
            display: inline;
            margin: 0;
        }

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 10px;
            color: #777;
        }

        .empty i {
            display: block;
            font-size: 35px;
            margin-bottom: 12px;
            color: #aab1c4;
        }

        @media (max-width: 800px) {
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

            .page-title {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">
        <img src="../img/LogoNichias.jpg" alt="Nichias Logo">
    </div>

    <a href="dashboard.php">
        <i class="fa-solid fa-chart-line"></i> Dashboard
    </a>

    <a href="admin_form.php">
        <i class="fa-solid fa-file-circle-plus"></i> Admin Form
    </a>

    <a href="admin_record.php">
        <i class="fa-solid fa-folder-open"></i> Admin Records
    </a>

    <a href="operator_record.php" class="active">
        <i class="fa-solid fa-users"></i> Operator Records
    </a>

    <a href="../logout.php">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
    </a>

</div>

<!-- MAIN CONTENT -->
<div class="main">

    <h1 class="page-title">
        <i class="fa-solid fa-users"></i> Operator Records
    </h1>

    <p class="subtitle">
        View, edit and delete operator records by month.
    </p>

    <input
        type="text"
        id="searchInput"
        class="search-box"
        placeholder="Search name, shift or date..."
        onkeyup="searchRecords()"
    >

    <?php if (empty($recordsByMonth)): ?>

        <div class="empty">
            <i class="fa-solid fa-folder-open"></i>
            No operator records found.
        </div>

    <?php else: ?>

        <?php foreach ($recordsByMonth as $monthKey => $monthData): ?>

            <div class="month-card">

                <button
                    type="button"
                    class="month-header"
                    onclick="toggleMonth(this)"
                >
                    <span class="month-info">
                        <i class="fa-regular fa-calendar"></i>
                        <?= e($monthData['label']) ?>

                        <span class="count">
                            <?= count($monthData['records']) ?> records
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-down"></i>
                </button>

                <div class="month-content">

                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Date</th>
                                <th>Operator Name</th>
                                <th>Shift</th>
                                <th>Output Powder</th>
                                <th>Qty Scrap</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $no = 1; ?>

                        <?php foreach ($monthData['records'] as $row): ?>

                            <tr class="record-row">
                                <td><?= $no++ ?></td>

                                <td><?= e($row['date']) ?></td>

                                <td class="search-name">
                                    <?= e($row['name']) ?>
                                </td>

                                <td class="search-shift">
                                    <?= e($row['shift']) ?>
                                </td>

                                <td>
                                    <?= e($row['output_powder']) ?>
                                </td>

                                <td>
                                    <?= e($row['qty_scarp']) ?>
                                </td>

                                <td>
                                    <div class="actions">

                                        <!-- EDIT: BUKA FORM DENGAN ID REKOD -->
                                        <a
                                            class="btn btn-edit"
                                            href="../operator/form.php?id=<?= (int)$row['id'] ?>"
                                            title="Edit this record"
                                        >
                                            <i class="fa-solid fa-pen-to-square"></i>
                                            Edit
                                        </a>

                                        <!-- DELETE: PADAM TERUS DALAM FILE INI -->
                                        <form
                                            method="POST"
                                            class="delete-form"
                                            onsubmit="return confirm('Are you sure you want to delete this record? This action cannot be undone.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="delete_id"
                                                value="<?= (int)$row['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-delete"
                                                title="Delete this record"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<script>
function toggleMonth(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('.fa-chevron-down');

    content.classList.toggle('show');

    if (icon) {
        icon.style.transform = content.classList.contains('show')
            ? 'rotate(180deg)'
            : 'rotate(0deg)';
    }
}

function searchRecords() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();

    document.querySelectorAll('.month-card').forEach(function(card) {
        const rows = card.querySelectorAll('.record-row');
        let found = 0;

        rows.forEach(function(row) {
            const text = row.innerText.toLowerCase();
            const match = text.includes(filter);

            row.style.display = match ? '' : 'none';

            if (match) {
                found++;
            }
        });

        card.style.display = found > 0 ? '' : 'none';

        if (filter !== '' && found > 0) {
            card.querySelector('.month-content').classList.add('show');
        }
    });
}
</script>

</body>
</html>
