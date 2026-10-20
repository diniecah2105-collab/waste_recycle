<?php

include "../db.php";


// CHECK ID
if (!isset($_GET['id'])) {
    die("Record ID not found.");
}

$id = intval($_GET['id']);


// DELETE RECORD
$sql = "DELETE FROM operator WHERE id = $id";


if (mysqli_query($conn, $sql)) {

    header("Location: record.php");
    exit;

} else {

    die("Failed to delete record: " . mysqli_error($conn));

}

?>