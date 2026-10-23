
<?php
session_start();
include __DIR__ . "/../db.php";

/* ========================================
   CHECK ADMIN LOGIN
======================================== */
if (
    !isset($_SESSION['admin_login']) ||
    $_SESSION['admin_login'] !== true
) {
    header("Location: ../login.php");
    exit;
}

/* ========================================
   DELETE OPERATOR RECORD
   DELETE TERUS DALAM FAIL INI
======================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_id'])
) {
    $id = (int) $_POST['delete_id'];

    if ($id > 0) {
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM operator WHERE id = ?"
        );

        if (!$stmt) {
            die("Database error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            die("Delete failed: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    }

    header("Location: operator_record.php");
    exit;
}

/* ========================================
   GET OPERATOR RECORDS
======================================== */
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

/* ========================================
   GROUP RECORDS BY MONTH
======================================== */
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

krsort($monthly_data);

$month_names = [
    1 => 'JANUARY',
    2 => 'FEBRUARY',
    3 => 'MARCH',
    4 => 'APRIL',
    5 => 'MAY',
    6 => 'JUNE',
    7 => 'JULY',
    8 => 'AUGUST',
    9 => 'SEPTEMBER',
    10 => 'OCTOBER',
    11 => 'NOVEMBER',
    12 => 'DECEMBER'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Operator Records - Waste Recycle</title>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f6fa;
    color: #222;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 235px;
    height: 100vh;
    background: #1e1768;
    color: white;
    padding-top: 25px;
    z-index: 10;
}

.logo-box {
    text-align: center;
    padding: 0 10px 25px;
}

.logo-box img {
    width: 65px;
    height: 65px;
    object-fit: contain;
    background: white;
    padding: 5px;
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

.menu {
    padding: 0 12px;
}

.menu a,
.logout a {
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
.menu a.active,
.logout a:hover {
    background: #2f4da0;
}

.menu-icon {
    width: 22px;
    text-align: center;
    font-size: 18px;
}

.logout {
    position: absolute;
    bottom: 20px;
    left: 12px;
    right: 12px;
}

/* MAIN */
.main {
    margin-left: 235px;
    padding: 30px;
    min-width: 0;
}

.top-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 22px;
    flex-wrap: wrap;
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

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #243b7a;
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
    box-shadow: 0 0 0 2px rgba(36,59,122,.10);
}

/* MONTH SECTION */
.month-section {
    margin-bottom: 12px;
}

.month-header {
    width: 100%;
    background: white;
    border: none;
    border-radius: 8px;
    padding: 17px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    transition: .2s;
    text-align: left;
}

.month-header:hover {
    background: #f8faff;
}

.month-left {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.month-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    background: #eaf0ff;
    color: #243b7a;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
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

.month-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
}

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
}

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

/* MONTH CONTENT */
.month-content {
    display: none;
    background: white;
    border-radius: 0 0 8px 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    overflow-x: auto;
}

.month-section.open .month-content {
    display: block;
}

/* TABLE */
table {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse;
    background: white;
}

th {
    background: #080e46;
    color: white;
    padding: 13px 12px;
    font-size: 14px;
    font-weight: bold;
    border-bottom: 2px solid #d4d6dd;
    border-right: 1px solid #ededed;
}

th:last-child {
    border-right: none;
}

td {
    padding: 12px;
    border-bottom: 1px solid #e3e7ee;
    border-right: 1px solid #edf0f5;
    text-align: center;
    font-size: 14px;
    background: white;
    color: #222;
}

td:last-child {
    border-right: none;
}

tbody tr:not(.total-row):hover td {
    background: #f7f9ff;
}

.action-cell {
    white-space: nowrap;
}

/* BUTTONS */
.edit-btn,
.delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 11px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
    cursor: pointer;
    transition: .2s;
    font-family: Arial, sans-serif;
}

.edit-btn {
    background: #0f0a58;
    color: white;
    border: 1px solid #0f0a58;
    margin-right: 5px;
}

.edit-btn:hover {
    background: #243b7a;
}

.delete-btn {
    background: #db0c1a;
    color: white;
    border: 1px solid #db0c1a;
}

.delete-btn:hover {
    background: #b80914;
}

/* TOTAL */
.total-row td {
    background: #080e46;
    color: white;
    font-weight: bold;
    border-top: 2px solid #d2d2d4;
    border-bottom: none;
}

.total-label {
    text-align: left !important;
    color: white;
    font-size: 14px;
    padding-left: 12px;
}

.total-value {
    font-size: 15px;
    color: white;
}

/* EMPTY STATES */
.empty,
.no-search {
    background: white;
    padding: 40px;
    text-align: center;
    color: #777;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

.empty i,
.no-search i {
    font-size: 32px;
    margin-bottom: 12px;
    color: #243b7a;
}

.no-search {
    display: none;
}

@media (max-width: 900px) {
    .main {
        padding: 20px;
    }

    .top-section {
        align-items: flex-start;
    }

    .top-actions,
    .search-box {
        width: 100%;
    }

    .search-box input {
        width: 100%;
    }
}

@media (max-width: 650px) {
    .sidebar {
        width: 180px;
    }

    .main {
        margin-left: 180px;
        padding: 15px;
    }

    .month-header {
        padding: 12px;
    }

    .month-actions {
        gap: 7px;
    }

    .month-export-btn {
        padding: 8px;
        font-size: 11px;
    }

    .month-name h2 {
        font-size: 15px;
    }

    .month-content {
        padding: 10px;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo-box">
        <img src="../img/LogoNichias.jpg" alt="NICHIAS Logo">
        <div class="system-title">ADMIN SYSTEM</div>
        <div class="system-subtitle">Waste Recycle</div>
    </div>

    <div class="sidebar-line"></div>

    <div class="menu">
        <a href="dashboard.php">
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

        <a href="operator_record.php" class="active">
            <span class="menu-icon">
                <i class="fa-solid fa-users-gear"></i>
            </span>
            <span>Operator Records</span>
        </a>
    </div>

    <div class="logout">
    <a href="../login.php"
       onclick="return confirm('Are you sure you want to logout?');">
        <span class="menu-icon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </span>
        <span>Logout</span>
    </a>
</div>

</div>

<!-- MAIN -->
<div class="main">

    <div class="top-section">
        <h1>Operator Records</h1>

        <div class="top-actions">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    id="monthSearch"
                    placeholder="Search month..."
                    autocomplete="off"
                >
            </div>
        </div>
    </div>

    <?php if (empty($monthly_data)): ?>

        <div class="empty">
            <i class="fa-solid fa-folder-open"></i>
            <br><br>
            No operator records found.
        </div>

    <?php else: ?>

        <div id="monthList">

        <?php $month_index = 0; ?>

        <?php foreach ($monthly_data as $month_key => $records): ?>

            <?php
            $date_object = strtotime($month_key . '-01');
            $year = date('Y', $date_object);
            $month_number = (int) date('n', $date_object);
            $month_name = $month_names[$month_number];

            $month_powder = 0;
            $month_scrap = 0;

            foreach ($records as $record) {
                $month_powder += (float)($record['output_powder'] ?? 0);
                $month_scrap += (float)($record['qty_scarp'] ?? 0);
            }

            $is_latest = ($month_index === 0);
            ?>

            <div
                class="month-section <?= $is_latest ? 'open' : '' ?>"
                data-month="<?= htmlspecialchars(
                    strtolower($month_name . ' ' . $year),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

                <!-- MONTH HEADER -->
                <button type="button" class="month-header">

                    <div class="month-left">

                        <div class="month-icon">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>

                        <div class="month-name">
                            <h2>
                                <?= htmlspecialchars($month_name) ?>
                                <?= htmlspecialchars($year) ?>
                            </h2>

                            <p><?= count($records) ?> record(s)</p>
                        </div>

                    </div>

                    <div class="month-actions">

                        <a
                            href="export_operator.php?month=<?= $month_number ?>&year=<?= $year ?>"
                            class="month-export-btn"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            Export Excel
                        </a>

                        <div class="month-arrow">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>

                    </div>

                </button>

                <!-- MONTH CONTENT -->
                <div class="month-content">

                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Shift</th>
                                <th>Date</th>
                                <th>Qty Powder (kg)</th>
                                <th>Qty Scrap (kg)</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php $no = 1; ?>

                        <?php foreach ($records as $row): ?>

                            <tr>
                                <td><?= $no++ ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['name'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['shift'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $row['date'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float)($row['output_powder'] ?? 0),
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float)($row['qty_scarp'] ?? 0),
                                        2
                                    ) ?>
                                </td>

                                <td class="action-cell">

                                    <!-- EDIT: KEKAL GUNA form.php -->
                                    <a
    href="../operator/form.php?id=<?= (int)$row['id'] ?>"
    class="edit-btn"
>
    <i class="fa-solid fa-pen-to-square"></i>
    Edit
</a>
                                    <!-- DELETE: DALAM FAIL INI -->
                                    <form
                                        method="POST"
                                        action="operator_record.php"
                                        style="display:inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this record?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="delete_id"
                                            value="<?= (int)$row['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-btn"
                                        >
                                            <i class="fa-solid fa-trash"></i>
                                            Delete
                                        </button>
                                    </form>

                                </td>
                            </tr>

                        <?php endforeach; ?>

                            <!-- MONTH TOTAL -->
                            <tr class="total-row">

                                <td colspan="4" class="total-label">
                                    TOTAL <?= htmlspecialchars($month_name) ?>
                                </td>

                                <td class="total-value">
                                    <?= number_format($month_powder, 2) ?> kg
                                </td>

                                <td class="total-value">
                                    <?= number_format($month_scrap, 2) ?> kg
                                </td>

                                <td></td>

                            </tr>

                        </tbody>
                    </table>

                </div>
            </div>

            <?php $month_index++; ?>

        <?php endforeach; ?>

        </div>

        <div id="noSearch" class="no-search">
            <i class="fa-solid fa-calendar-xmark"></i>
            <br><br>
            No month found.
        </div>

    <?php endif; ?>

</div>

<script>
/* ========================================
   ACCORDION
======================================== */
document.querySelectorAll('.month-header').forEach(function(header) {

    header.addEventListener('click', function(event) {

        // Jangan buka/tutup bulan apabila Export Excel diklik.
        if (event.target.closest('.month-export-btn')) {
            return;
        }

        const section = this.closest('.month-section');
        section.classList.toggle('open');

    });

});

/* ========================================
   SEARCH MONTH
======================================== */
const searchInput = document.getElementById('monthSearch');

if (searchInput) {

    searchInput.addEventListener('input', function() {

        const keyword = this.value.toLowerCase().trim();
        const sections = document.querySelectorAll('.month-section');
        const noSearch = document.getElementById('noSearch');

        let found = false;

        sections.forEach(function(section) {

            const month = section.dataset.month || '';

            if (month.includes(keyword)) {
                section.style.display = '';
                found = true;
            } else {
                section.style.display = 'none';
            }

        });

        if (noSearch) {
            noSearch.style.display = found ? 'none' : 'block';
        }

    });

}
</script>

</body>
</html>
