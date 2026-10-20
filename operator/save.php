<?php

include __DIR__ . "/../db.php";

$id = $_POST['id'] ?? '';

$powder_date = $_POST['powder_date'] ?? '';
$scrap_date = $_POST['scrap_date'] ?? '';

$name = $_POST['name'] ?? '';
$shift = $_POST['shift'] ?? '';

$output_powder = $_POST['output_powder'] ?? '';
$qty_scarp = $_POST['qty_scarp'] ?? '';


// ===============================
// ESCAPE DATA
// ===============================

$powder_date = mysqli_real_escape_string($conn, $powder_date);
$scrap_date = mysqli_real_escape_string($conn, $scrap_date);

$name = mysqli_real_escape_string($conn, $name);
$shift = mysqli_real_escape_string($conn, $shift);

$output_powder = mysqli_real_escape_string($conn, $output_powder);
$qty_scarp = mysqli_real_escape_string($conn, $qty_scarp);


// ===============================
// EDIT EXISTING RECORD
// ===============================

if ($id != '') {

    $sql = "
        UPDATE operator SET

            powder_date = '$powder_date',
            scrap_date = '$scrap_date',

            name = '$name',
            shift = '$shift',

            output_powder = '$output_powder',
            qty_scarp = '$qty_scarp'

        WHERE id = '$id'
    ";

    if (!mysqli_query($conn, $sql)) {
        die("Update Error: " . mysqli_error($conn));
    }

}


// ===============================
// NEW RECORD
// ===============================

else {

    $sql = "
        INSERT INTO operator
        (
            date,
            powder_date,
            scrap_date,
            name,
            shift,
            output_powder,
            qty_scarp
        )

        VALUES
        (
            '$powder_date',
            '$powder_date',
            '$scrap_date',
            '$name',
            '$shift',
            '$output_powder',
            '$qty_scarp'
        )
    ";

    if (!mysqli_query($conn, $sql)) {
        die("Save Error: " . mysqli_error($conn));
    }

}


// ===============================
// BACK TO RECORD PAGE
// ===============================

header("Location: record.php");
exit;

?>