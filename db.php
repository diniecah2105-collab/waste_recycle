<?php

$conn = mysqli_connect("localhost", "root", "MIS_1990", "waste_recycle");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>