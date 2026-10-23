
<?php
session_start();
include "../db.php";

// Check admin login
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$editMode = false;
$recordId = 0;
$editRecord = null;

$monthNames = [
    "January", "February", "March", "April",
    "May", "June", "July", "August",
    "September", "October", "November", "December"
];

if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
    $recordId = (int)$_GET['id'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM admin WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $recordId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editRecord = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$editRecord) {
        die("Record not found.");
    }

    $editMode = true;
}

$tomboNo = $editRecord['tombo_no'] ?? '';
$selectedMonth = $editRecord['month'] ?? '';
$selectedYear = (int)($editRecord['year'] ?? date('Y'));
$workingDays = $editRecord['working_days'] ?? 0;
$ave1 = $editRecord['ave_1'] ?? 0;
$ave2 = $editRecord['ave_2'] ?? 0;

$openingBalance = [];
$dateData = [];

if ($editMode) {
    $openingBalance = json_decode($editRecord['opening_balance'] ?? '{}', true);
    $dateData = json_decode($editRecord['date_data'] ?? '{}', true);

    if (!is_array($openingBalance)) $openingBalance = [];
    if (!is_array($dateData)) $dateData = [];
}

$items = [
    "from_production_theoretical" => "From Production-Theoretical",
    "from_production_actual" => "From Production-Actual",
    "balance_waste_before_crush" => "Balance Waste Before Crush",
    "crushing_process_theoretical" => "Crushing Process-Theoretical",
    "crushing_process_actual" => "Crushing Process-Actual",
    "balance_waste_after_crush" => "Balance Waste After Crush",
    "in_theoretical" => "In-Theoretical",
    "theoretical_powder" => "Theoretical Powder",
    "in_actual" => "In-Actual",
    "actual_powder" => "Actual Powder",
    "out_qty" => "Out",
    "phoenix" => "Phoenix",
    "daiko" => "DAIKO (D 6000)",
    "balance_powder" => "Balance Powder"
];

$autoItems = [
    "balance_waste_before_crush",
    "balance_waste_after_crush",
    "theoretical_powder",
    "actual_powder",
    "out_qty",
    "balance_powder"
];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$ave2Display = str_replace(',', '', (string)$ave2);

if ($ave2Display !== '' && is_numeric($ave2Display)) {
    $ave2Display = number_format((float)$ave2Display, 0, '.', ',');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= $editMode ? 'Edit Admin Record' : 'Admin Form' ?> - Waste Recycle</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7fb;
    color: #333;
    overflow-x: hidden;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 235px;
    height: 100vh;
    background: #1e1768;
    color: white;
    padding-top: 25px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.logo-box {
    text-align: center;
    padding: 0 12px 25px;
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
    flex-shrink: 0;
    background: rgba(255,255,255,.25);
    margin: 0 18px 18px;
}

.menu {
    padding: 0 12px;
    flex: 1;
}

.menu a,
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

.menu a {
    margin-bottom: 5px;
}

.menu a:hover,
.menu a.active,
.logout a:hover {
    background: #2f4da0;
}

.menu-icon {
    width: 22px;
    flex-shrink: 0;
    text-align: center;
    font-size: 18px;
}

.logout {
    margin: 15px 12px 20px;
}

/* MAIN CONTENT */
.main {
    margin-left: 235px;
    width: calc(100% - 235px);
    min-width: 0;
    min-height: 100vh;
    padding: 30px;
}

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    color: #243b7a;
    font-size: 28px;
    line-height: 1.3;
}

.page-header p {
    margin-top: 7px;
    color: #777;
    font-size: 14px;
    line-height: 1.5;
}

.edit-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff4df;
    border: 1px solid #f0d49a;
    color: #805a12;
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 13px;
}

/* FORM CARD */
.form-card {
    width: 100%;
    min-width: 0;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,.07);
}

.information-section {
    border: 1px solid #e0e4eb;
    border-radius: 10px;
    padding: 20px;
    background: #fafbfc;
    margin-bottom: 25px;
}

.information-title {
    font-size: 14px;
    font-weight: bold;
    color: #243b7a;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e1e5ec;
}

.information-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(0, .8fr)
                           minmax(0, 1fr) minmax(0, 1fr);
    gap: 15px;
}

.form-group {
    min-width: 0;
    background: white;
    border: 1px solid #dfe3ea;
    border-radius: 8px;
    padding: 14px;
    min-height: 105px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: bold;
    color: #4b5563;
    margin-bottom: 8px;
    line-height: 1.5;
}

.form-group input,
.form-group select {
    width: 100%;
    min-width: 0;
    height: 38px;
    border: 1px solid #d2d7df;
    border-radius: 5px;
    padding: 8px 10px;
    font-size: 13px;
    background: white;
    color: #333;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #243b7a;
}

.ave-box {
    width: 100%;
    height: 76px;
    border: 1px solid #d6d9e2;
    border-radius: 6px;
    background: white;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.ave-percent-wrap {
    position: relative;
    width: 100%;
    height: 37px;
}

.ave-box input {
    width: 100%;
    height: 37px;
    border: none;
    text-align: center;
}

.ave-percent-wrap input {
    padding-right: 30px;
}

.ave-line {
    width: 100%;
    height: 1px;
    background: #d6d9e2;
}

.percent-symbol {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
}

#aveResult {
    background: #f1f4f9;
    color: #243b7a;
    font-weight: bold;
}

/* ADD DATE */
.add-date-section {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.date-input-box {
    display: flex;
    flex-direction: column;
}

.date-input-box label {
    font-size: 12px;
    font-weight: bold;
    color: #4b5563;
    margin-bottom: 7px;
}

.date-input-box input {
    height: 38px;
    width: 180px;
    border: 1px solid #d2d7df;
    border-radius: 5px;
    padding: 8px 10px;
}

.add-date-btn {
    height: 38px;
    padding: 0 18px;
    border: none;
    background: #243b7a;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

.add-date-btn:hover {
    background: #2f4da0;
}

/* TABLE */
.table-wrapper {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overscroll-behavior-x: contain;
    border: 1px solid #dfe3ea;
    background: white;
}

.record-table {
    border-collapse: collapse;
    min-width: 1100px;
    width: 100%;
}

.record-table th,
.record-table td {
    border: 1px solid #e0e3e8;
    padding: 8px;
    text-align: center;
}

.record-table th {
    background: #243b7a;
    color: white;
    font-size: 12px;
    height: 45px;
}

.record-table td {
    background: white;
    font-size: 12px;
}

.record-table tbody tr:nth-child(even) td {
    background: #fafbfc;
}

.items-header,
.item-name {
    width: 280px;
    min-width: 280px;
}

.opening-header,
.closing-header,
.opening-cell,
.closing-cell {
    width: 160px;
    min-width: 160px;
}

.item-name {
    text-align: left !important;
    font-weight: 600;
    background: #f4f7fb !important;
    position: sticky;
    left: 0;
    z-index: 2;
}

.closing-cell {
    background: #f7f9fc !important;
}

.date-header,
.date-cell {
    min-width: 160px;
}

.record-table input {
    width: 100%;
    min-width: 0;
    height: 32px;
    border: 1px solid #d2d7df;
    border-radius: 4px;
    padding: 5px 7px;
    text-align: center;
    font-size: 12px;
}

.auto-field {
    background: #f1f4f9 !important;
    color: #243b7a !important;
    font-weight: bold;
}

.closing-cell input {
    background: #f1f4f9 !important;
    color: #243b7a !important;
    font-weight: bold;
}

.daiko-input {
    background: white !important;
    color: #333 !important;
    font-weight: normal !important;
}

.record-table tr[data-item="out_qty"] td,
.record-table tr[data-item="balance_powder"] td {
    border-top: 2px solid #243b7a !important;
    border-bottom: 2px solid #243b7a !important;
}

.record-table tr[data-item="out_qty"] .item-name,
.record-table tr[data-item="balance_powder"] .item-name {
    font-weight: bold;
    color: #243b7a;
}

.phoenix-daiko-closing {
    vertical-align: middle !important;
}

.remove-date {
    display: block;
    margin: 5px auto 0;
    padding: 4px 8px;
    border: none;
    background: #d9534f;
    color: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 10px;
}

/* BUTTONS */
.button-area {
    margin-top: 22px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-wrap: wrap;
}

.save-btn,
.cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    font-size: 13px;
}

.save-btn {
    background: #243b7a;
    color: white;
    border: none;
}

.save-btn:hover {
    background: #2f4da0;
}

.cancel-btn {
    background: #6c757d;
    color: white;
}

.cancel-btn:hover {
    background: #565e64;
}

/* RESPONSIVE */
@media (max-width: 1200px) {
    .information-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .sidebar {
        width: 200px;
    }

    .main {
        margin-left: 200px;
        width: calc(100% - 200px);
        padding: 15px;
    }

    .form-card {
        padding: 15px;
    }

    .information-section {
        padding: 12px;
    }

    .information-grid {
        grid-template-columns: minmax(0, 1fr);
    }

    .page-header h1 {
        font-size: 23px;
    }

    .save-btn,
    .cancel-btn {
        padding: 11px 15px;
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
            <span class="menu-icon"><i class="fa-solid fa-chart-pie"></i></span>
            <span>Dashboard</span>
        </a>

        <a href="admin_form.php" class="active">
            <span class="menu-icon"><i class="fa-solid fa-file-pen"></i></span>
            <span>Admin Form</span>
        </a>

        <a href="admin_record.php">
            <span class="menu-icon"><i class="fa-solid fa-folder-open"></i></span>
            <span>Admin Records</span>
        </a>

        <a href="operator_record.php">
            <span class="menu-icon"><i class="fa-solid fa-users-gear"></i></span>
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

<!-- MAIN CONTENT -->
<div class="main">

    <div class="page-header">
        <h1><?= $editMode ? 'Edit Admin Record' : 'Admin Form' ?></h1>
        <p>
            <?php if ($editMode): ?>
                Update existing Waste Recycle record for
                <?= h($selectedMonth . ' ' . $selectedYear) ?>
            <?php else: ?>
                Enter Waste Recycle production record.
            <?php endif; ?>
        </p>
    </div>

    <?php if ($editMode): ?>
        <div class="edit-notice">
            <i class="fa-solid fa-circle-info"></i>
            <span>
                Editing record ID <?= $recordId ?>.
                Check the values before saving.
            </span>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form action="save_admin.php" method="POST" id="adminForm">

            <input type="hidden" name="record_id"
                   value="<?= $editMode ? $recordId : '' ?>">

            <input type="hidden" name="edit_mode"
                   value="<?= $editMode ? '1' : '0' ?>">

            <!-- PRODUCTION INFORMATION -->
            <div class="information-section">
                <div class="information-title">Production Information</div>

                <div class="information-grid">

                    <div class="form-group">
                        <label>Tombo No. 1995 Production Estimation Qty (Kgs)</label>
                        <input type="text" name="tombo_no" id="tomboNo"
                               placeholder="Enter quantity"
                               inputmode="decimal" autocomplete="off"
                               value="<?= h($tomboNo) ?>"
                               oninput="calculateAVE()">
                    </div>

                    <div class="form-group">
                        <label>Ave</label>

                        <div class="ave-box">
                            <div class="ave-percent-wrap">
                                <input type="number" name="ave[]" id="avePercent"
                                       value="<?= h($ave1) ?>" step="0.01"
                                       oninput="calculateAVE()">
                                <span class="percent-symbol">%</span>
                            </div>

                            <div class="ave-line"></div>

                            <input type="text" name="ave[]" id="aveResult"
                                   value="<?= h($ave2Display) ?>" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Month</label>

                        <select name="month" id="monthSelect" required>
                            <option value="">Select Month</option>

                            <?php foreach ($monthNames as $monthName): ?>
                                <option value="<?= h($monthName) ?>"
                                    <?= $selectedMonth === $monthName ? 'selected' : '' ?>>
                                    <?= h($monthName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="hidden" name="year"
                               value="<?= $selectedYear ?>">
                    </div>

                    <div class="form-group">
                        <label>Working Days</label>
                        <input type="number" name="working_days"
                               value="<?= h($workingDays) ?>" min="0">
                    </div>

                </div>
            </div>

            <!-- ADD DATE -->
            <div class="add-date-section">
                <div class="date-input-box">
                    <label for="dateInput">Select Date</label>
                    <input type="date" id="dateInput">
                </div>

                <button type="button" class="add-date-btn" onclick="addDate()">
                    <i class="fa-solid fa-plus"></i> Add Date
                </button>
            </div>

            <!-- RECORD TABLE -->
            <div class="table-wrapper">
                <table class="record-table" id="recordTable">

                    <thead>
                        <tr id="tableHeader">
                            <th class="items-header">Items</th>
                            <th class="opening-header">Opening Balance</th>
                            <th class="closing-header">Closing Balance</th>
                        </tr>
                    </thead>

                    <tbody id="tableBody">

                    <?php foreach ($items as $key => $label): ?>
                        <tr data-item="<?= h($key) ?>">

                            <td class="item-name"><?= h($label) ?></td>

                            <td class="opening-cell">
                                <input type="number" step="0.01"
                                       name="opening_balance[<?= h($key) ?>]"
                                       value="<?= h($openingBalance[$key] ?? ($editMode ? '' : '0')) ?>"
                                       class="opening-input"
                                       oninput="calculateAll()">
                            </td>

                            <?php if ($key === "phoenix"): ?>

                                <td class="closing-cell phoenix-daiko-closing"
                                    rowspan="2">
                                    <input type="text"
                                           name="closing_balance[phoenix_daiko]"
                                           value="" readonly
                                           class="auto-field"
                                           id="phoenixDaikoClosing">
                                </td>

                            <?php elseif ($key === "daiko"): ?>

                                <!-- Closing cell merged with Phoenix -->

                            <?php elseif ($key === "out_qty"): ?>

                                <td class="closing-cell">
                                    <input type="text" value="TOTAL"
                                           readonly class="auto-field">
                                </td>

                            <?php else: ?>

                                <td class="closing-cell">
                                    <input type="text"
                                           name="closing_balance[<?= h($key) ?>]"
                                           value="" readonly
                                           class="auto-field"
                                           data-closing-item="<?= h($key) ?>">
                                </td>

                            <?php endif; ?>

                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

            <!-- FORM BUTTONS -->
            <div class="button-area">

                <a href="<?= $editMode
                    ? 'admin_view.php?month=' . urlencode($selectedMonth) . '&year=' . $selectedYear
                    : 'admin_record.php' ?>"
                   class="cancel-btn">
                    <i class="fa-solid fa-arrow-left"></i> Cancel
                </a>

                <button type="submit" class="save-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <?= $editMode ? 'Save Changes' : 'Save Record' ?>
                </button>

            </div>

        </form>
    </div>
</div>

<script>
const editMode = <?= $editMode ? 'true' : 'false' ?>;

const savedDateData = <?= json_encode(
    $dateData,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const savedOpeningBalance = <?= json_encode(
    $openingBalance,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const autoItems = [
    "balance_waste_before_crush",
    "balance_waste_after_crush",
    "theoretical_powder",
    "actual_powder",
    "out_qty",
    "balance_powder"
];

/* AVE calculation */
function calculateAVE() {
    const tomboText = document.getElementById("tomboNo").value.trim();
    const percentText = document.getElementById("avePercent").value.trim();

    const tombo = parseFloat(tomboText.replace(/,/g, ""));
    const percent = parseFloat(percentText);

    if (
        tomboText === "" ||
        percentText === "" ||
        !Number.isFinite(tombo) ||
        !Number.isFinite(percent)
    ) {
        document.getElementById("aveResult").value = "";
        return;
    }

    const result = Math.round(tombo * percent / 100);

    document.getElementById("aveResult").value =
        result.toLocaleString("en-US");
}

function formatDate(date) {
    const parts = date.split("-");

    return parts.length === 3
        ? parts[2] + "/" + parts[1] + "/" + parts[0]
        : date;
}

/* Add date column */
function addDate(dateToAdd = null) {
    const dateInput = document.getElementById("dateInput");
    const date = dateToAdd || dateInput.value;

    if (!date) {
        alert("Please select a date.");
        return;
    }

    if (
        document.querySelector(
            '.date-header[data-date="' + date + '"]'
        )
    ) {
        return;
    }

    const th = document.createElement("th");
    th.className = "date-header";
    th.dataset.date = date;

    const label = document.createElement("div");
    label.textContent = formatDate(date);
    th.appendChild(label);

    const removeButton = document.createElement("button");
    removeButton.type = "button";
    removeButton.className = "remove-date";
    removeButton.textContent = "Remove";

    removeButton.addEventListener("click", function() {
        removeDate(date);
    });

    th.appendChild(removeButton);

    const header = document.getElementById("tableHeader");
    header.insertBefore(th, document.querySelector(".closing-header"));

    document.querySelectorAll("#tableBody tr").forEach(row => {
        const item = row.dataset.item;
        const td = document.createElement("td");

        td.className = "date-cell";
        td.dataset.date = date;

        const input = document.createElement("input");
        input.dataset.item = item;
        input.dataset.date = date;

        if (item === "daiko") {
            input.type = "number";
            input.name = `date_data[${date}][daiko]`;
            input.value = editMode ? "" : "0";
            input.step = "0.01";
            input.className = "daiko-input";
            input.addEventListener("input", calculateAll);
        } else {
            input.name = `date_data[${date}][${item}]`;

            if (autoItems.includes(item)) {
                input.type = "text";
                input.value = "";
                input.readOnly = true;
                input.className = "auto-field";
            } else {
                input.type = "number";
                input.value = "";
                input.step = "0.01";
                input.addEventListener("input", calculateAll);
            }
        }

        td.appendChild(input);

        const closingCell = row.querySelector(".closing-cell");

        if (closingCell) {
            row.insertBefore(td, closingCell);
        } else {
            row.appendChild(td);
        }
    });

    if (!dateToAdd) {
        dateInput.value = "";
        calculateAll();
    }
}

/* Remove date column */
function removeDate(date) {
    if (!confirm("Remove this date?")) return;

    document.querySelector(
        '.date-header[data-date="' + date + '"]'
    )?.remove();

    document.querySelectorAll(
        '.date-cell[data-date="' + date + '"]'
    ).forEach(cell => cell.remove());

    calculateAll();
}

function getInput(date, item) {
    return document.querySelector(
        `input[data-date="${date}"][data-item="${item}"]`
    );
}

function getValue(date, item) {
    const input = getInput(date, item);

    if (!input || input.value.trim() === "") {
        return 0;
    }

    const value = parseFloat(input.value.replace(/,/g, ""));

    return Number.isFinite(value) ? value : 0;
}

function setValue(date, item, value) {
    const input = getInput(date, item);

    if (input) {
        input.value = Number(value).toFixed(2);
    }
}

function getDates() {
    return Array.from(
        document.querySelectorAll("#tableHeader th[data-date]")
    )
    .map(th => th.dataset.date)
    .filter(Boolean)
    .sort((a, b) => a.localeCompare(b));
}

/* Load existing record when editing */
function loadExistingData() {
    if (!editMode) return;

    Object.keys(savedDateData)
        .sort((a, b) => a.localeCompare(b))
        .forEach(date => addDate(date));

    Object.keys(savedDateData).forEach(date => {
        const daily = savedDateData[date];

        if (!daily || typeof daily !== "object") return;

        Object.keys(daily).forEach(item => {
            const input = getInput(date, item);

            if (input) {
                input.value = daily[item] ?? "";
            }
        });
    });

    Object.keys(savedOpeningBalance).forEach(item => {
        const input = document.querySelector(
            `input[name="opening_balance[${item}]"]`
        );

        if (input) {
            input.value = savedOpeningBalance[item] ?? "";
        }
    });

    calculateAll();
}

/* Closing balance calculations */
function calculateClosingBalance() {
    const dates = getDates();

    const sumItems = [
        "from_production_theoretical",
        "from_production_actual",
        "crushing_process_theoretical",
        "crushing_process_actual",
        "in_theoretical",
        "in_actual"
    ];

    document.querySelectorAll("#tableBody tr").forEach(row => {
        const item = row.dataset.item;

        if (item === "daiko") return;

        const closingInput = item === "phoenix"
            ? document.getElementById("phoenixDaikoClosing")
            : row.querySelector(".closing-cell input");

        if (!closingInput) return;

        if (item === "out_qty") {
            closingInput.value = "TOTAL";
            return;
        }

        if (item === "phoenix") {
            let total = 0;

            dates.forEach(date => {
                total += getValue(date, "phoenix");
                total += getValue(date, "daiko");
            });

            closingInput.value = total.toFixed(2);
            return;
        }

        if (sumItems.includes(item)) {
            let total = 0;

            dates.forEach(date => {
                total += getValue(date, item);
            });

            closingInput.value = total.toFixed(2);
        } else if (dates.length > 0) {
            closingInput.value =
                getValue(dates[dates.length - 1], item).toFixed(2);
        }
    });
}

/* Automatic production calculations */
function calculateAll() {
    const dates = getDates();

    if (dates.length === 0) {
        calculateClosingBalance();
        return;
    }

    const openingActual = parseFloat(
        document.querySelector(
            'input[name="opening_balance[actual_powder]"]'
        )?.value
    ) || 0;

    const openingBefore = parseFloat(
        document.querySelector(
            'input[name="opening_balance[balance_waste_before_crush]"]'
        )?.value
    ) || 0;

    const openingAfter = parseFloat(
        document.querySelector(
            'input[name="opening_balance[balance_waste_after_crush]"]'
        )?.value
    ) || 0;

    let previousTheoretical = 0;
    let previousBalanceBefore = openingBefore;
    let previousBalanceAfter = openingAfter;
    let previousBalancePowder = openingActual;

    dates.forEach((date, index) => {
        const productionActual =
            getValue(date, "from_production_actual");

        const crushingTheoretical =
            getValue(date, "crushing_process_theoretical");

        const crushingActual =
            getValue(date, "crushing_process_actual");

        const inTheoretical = getValue(date, "in_theoretical");
        const inActual = getValue(date, "in_actual");
        const phoenix = getValue(date, "phoenix");
        const daiko = getValue(date, "daiko");

        /* Balance Waste Before Crush */
        const balanceBefore = index === 0
            ? openingBefore + productionActual
            : previousBalanceBefore + productionActual;

        setValue(date, "balance_waste_before_crush", balanceBefore);

        /* Balance Waste After Crush */
        const crushingUsed = crushingActual !== 0
            ? crushingActual
            : crushingTheoretical;

        const balanceAfter = index === 0
            ? openingAfter + crushingUsed
            : previousBalanceAfter + crushingUsed;

        setValue(date, "balance_waste_after_crush", balanceAfter);

        /* Actual Powder */
        const actualPowder = index === 0
            ? openingActual + crushingActual + inActual
            : previousBalancePowder + crushingActual + inActual;

        setValue(date, "actual_powder", actualPowder);

        /* Theoretical Powder */
        const theoreticalPowder = index === 0
            ? inTheoretical
            : previousTheoretical + inTheoretical;

        setValue(date, "theoretical_powder", theoreticalPowder);

        /* Out = Phoenix + DAIKO */
        const out = phoenix + daiko;
        setValue(date, "out_qty", out);

        /* Balance Powder */
        const balancePowder = actualPowder - out;
        setValue(date, "balance_powder", balancePowder);

        previousBalanceBefore = balanceBefore;
        previousBalanceAfter = balanceAfter;
        previousTheoretical = theoreticalPowder;
        previousBalancePowder = balancePowder;
    });

    calculateClosingBalance();
}

/* Initialize form */
document.addEventListener("DOMContentLoaded", function() {
    if (editMode) {
        loadExistingData();
    } else {
        calculateAVE();
        calculateAll();
    }
});

document.getElementById("adminForm").addEventListener("input", function(event) {
    if (event.target.matches('input[name^="opening_balance"]')) {
        calculateAll();
    }
});

document.getElementById("adminForm").addEventListener("submit", function(event) {
    if (!document.getElementById("monthSelect").value) {
        event.preventDefault();
        alert("Please select a month.");
        return;
    }

    calculateAVE();
    calculateAll();
});
</script>

</body>
</html>
