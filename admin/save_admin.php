<?php
include "../db.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

/*
=========================================================
1. AMBIL DATA FORM
=========================================================
*/

$record_id = isset($_POST["record_id"])
    ? (int)$_POST["record_id"]
    : 0;

$month = trim($_POST["month"] ?? "");

$year = isset($_POST["year"])
    ? (int)$_POST["year"]
    : (int)date("Y");

$tombo_no = trim($_POST["tombo_no"] ?? "");
$working_days = $_POST["working_days"] ?? 0;

/*
=========================================================
2. VALIDATE MONTH & YEAR
=========================================================
*/

$valid_months = [
    "January", "February", "March", "April",
    "May", "June", "July", "August",
    "September", "October", "November", "December"
];

if (!in_array($month, $valid_months, true)) {
    die("Invalid month.");
}

if ($year < 2000 || $year > 2100) {
    die("Invalid year.");
}

/*
=========================================================
3. AVE - BUANG KOMA SEBELUM SIMPAN
=========================================================
*/

$ave = $_POST["ave"] ?? [];

if (!is_array($ave)) {
    $ave = [];
}

$ave_1 = trim((string)($ave[0] ?? "0"));
$ave_2 = trim((string)($ave[1] ?? "0"));

$ave_1 = str_replace(",", "", $ave_1);
$ave_2 = str_replace(",", "", $ave_2);

if ($ave_1 === "" || !is_numeric($ave_1)) {
    $ave_1 = 0;
} else {
    $ave_1 = (float)$ave_1;
}

if ($ave_2 === "" || !is_numeric($ave_2)) {
    $ave_2 = 0;
} else {
    $ave_2 = (float)$ave_2;
}

/*
=========================================================
4. OPENING BALANCE
=========================================================
*/

$opening_balance = $_POST["opening_balance"] ?? [];

if (!is_array($opening_balance)) {
    $opening_balance = [];
}

/*
=========================================================
5. DATA HARIAN
=========================================================
*/

$date_data = $_POST["date_data"] ?? [];

if (!is_array($date_data)) {
    $date_data = [];
}

/*
=========================================================
6. BERSIHKAN OPENING BALANCE
=========================================================
*/

$clean_opening_balance = [];

foreach ($opening_balance as $key => $value) {
    if (is_array($value)) {
        continue;
    }

    $clean_opening_balance[$key] =
        ($value === null || trim((string)$value) === "")
        ? 0
        : $value;
}

/*
=========================================================
7. BERSIHKAN DATA HARIAN
=========================================================
*/

$clean_date_data = [];

foreach ($date_data as $date => $items) {
    $date_object = DateTime::createFromFormat("!Y-m-d", $date);

    if (
        !$date_object ||
        $date_object->format("Y-m-d") !== $date
    ) {
        continue;
    }

    if (!is_array($items)) {
        continue;
    }

    $clean_date_data[$date] = [];

    foreach ($items as $key => $value) {
        if (is_array($value)) {
            continue;
        }

        $clean_date_data[$date][$key] =
            ($value === null || trim((string)$value) === "")
            ? 0
            : $value;
    }
}

/*
=========================================================
8. SUSUN TARIKH
=========================================================
*/

uksort($clean_date_data, function($a, $b) {
    return strcmp($a, $b);
});

/*
=========================================================
9. CONVERT DATA KE JSON
=========================================================
*/

$opening_balance_json = json_encode(
    $clean_opening_balance,
    JSON_UNESCAPED_UNICODE
);

if ($opening_balance_json === false) {
    die("Error converting opening balance: " . json_last_error_msg());
}

$date_data_json = json_encode(
    $clean_date_data,
    JSON_UNESCAPED_UNICODE
);

if ($date_data_json === false) {
    die("Error converting date data: " . json_last_error_msg());
}

/*
=========================================================
10. SEMAK REKOD SEDIA ADA
=========================================================
*/

if ($record_id > 0) {
    $check_sql = "
        SELECT id
        FROM admin
        WHERE id = ?
        LIMIT 1
    ";

    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $record_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $existing_record = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if (!$existing_record) {
        die("Record yang hendak diedit tidak dijumpai.");
    }

    $duplicate_sql = "
        SELECT id
        FROM admin
        WHERE month = ?
          AND year = ?
          AND id <> ?
        LIMIT 1
    ";

    $duplicate_stmt = mysqli_prepare($conn, $duplicate_sql);
    mysqli_stmt_bind_param(
        $duplicate_stmt,
        "sii",
        $month,
        $year,
        $record_id
    );
    mysqli_stmt_execute($duplicate_stmt);
    $duplicate_result = mysqli_stmt_get_result($duplicate_stmt);
    $duplicate_record = mysqli_fetch_assoc($duplicate_result);
    mysqli_stmt_close($duplicate_stmt);

    if ($duplicate_record) {
        die("Rekod bagi bulan dan tahun ini sudah wujud.");
    }
} else {
    $check_sql = "
        SELECT id
        FROM admin
        WHERE month = ?
          AND year = ?
        LIMIT 1
    ";

    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "si", $month, $year);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $existing_record = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);

    if ($existing_record) {
        $record_id = (int)$existing_record["id"];
    }
}

/*
=========================================================
11. UPDATE REKOD
=========================================================
*/

if ($record_id > 0) {
    $sql = "
        UPDATE admin
        SET
            month = ?,
            year = ?,
            tombo_no = ?,
            ave_1 = ?,
            ave_2 = ?,
            working_days = ?,
            opening_balance = ?,
            date_data = ?
        WHERE id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sisddissi",
        $month,
        $year,
        $tombo_no,
        $ave_1,
        $ave_2,
        $working_days,
        $opening_balance_json,
        $date_data_json,
        $record_id
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/*
=========================================================
12. INSERT REKOD BAHARU
=========================================================
*/

else {
    $sql = "
        INSERT INTO admin (
            month,
            year,
            tombo_no,
            ave_1,
            ave_2,
            working_days,
            opening_balance,
            date_data
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sisddiss",
        $month,
        $year,
        $tombo_no,
        $ave_1,
        $ave_2,
        $working_days,
        $opening_balance_json,
        $date_data_json
    );

    mysqli_stmt_execute($stmt);
    $record_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
}

/*
=========================================================
13. REDIRECT SELEPAS SIMPAN
=========================================================
*/

header(
    "Location: admin_view.php?month=" .
    urlencode($month) .
    "&year=" .
    urlencode((string)$year)
);

exit;
?>