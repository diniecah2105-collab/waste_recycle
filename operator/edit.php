<?php

include "../db.php";


// GET ID
if (!isset($_GET['id'])) {
    die("Record ID not found.");
}

$id = intval($_GET['id']);


// GET RECORD
$sql = "SELECT * FROM operator WHERE id = $id";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Record not found.");
}

$row = mysqli_fetch_assoc($result);


// UPDATE RECORD
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $shift = $_POST['shift'];
    $date = $_POST['date'];
    $powder = $_POST['output_powder'];
    $scrap = $_POST['qty_scarp'];


    $update = "UPDATE operator SET
                name = '$name',
                shift = '$shift',
                date = '$date',
                output_powder = '$powder',
                qty_scarp = '$scrap'
               WHERE id = $id";


    if (mysqli_query($conn, $update)) {

        header("Location: record.php");
        exit;

    } else {

        $error = "Failed to update record: " . mysqli_error($conn);

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Operator Record</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f4f7f9;
}

.container {
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.form-box {
    width: 550px;
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.12);
}

h1 {
    text-align: center;
    color: #008B8B;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
    color: #333;
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

input:focus,
select:focus {
    outline: none;
    border-color: #008B8B;
}

.button-area {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button,
.back-btn {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.update-btn {
    background: #008B8B;
    color: white;
}

.update-btn:hover {
    background: #006f6f;
}

.back-btn {
    background: #777;
    color: white;
}

.back-btn:hover {
    background: #555;
}

.error {
    background: #ffe0e0;
    color: #c00;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

</style>

</head>

<body>

<div class="container">

    <div class="form-box">

        <h1>Edit Operator Record</h1>


        <?php if (isset($error)) { ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php } ?>


        <form method="POST">


            <!-- NAME -->

            <div class="form-group">

                <label>Name</label>

                <input
                    type="text"
                    name="name"
                    value="<?= htmlspecialchars($row['name'] ?? '') ?>"
                    required
                >

            </div>


            <!-- SHIFT -->

            <div class="form-group">

                <label>Shift</label>

                <select name="shift" required>

                    <option value="">Select Shift</option>

                    <option
                        value="Morning"
                        <?= (($row['shift'] ?? '') == 'Morning') ? 'selected' : '' ?>
                    >
                        Morning
                    </option>

                    <option
                        value="Afternoon"
                        <?= (($row['shift'] ?? '') == 'Afternoon') ? 'selected' : '' ?>
                    >
                        Afternoon
                    </option>

                    <option
                        value="Night"
                        <?= (($row['shift'] ?? '') == 'Night') ? 'selected' : '' ?>
                    >
                        Night
                    </option>

                </select>

            </div>


            <!-- DATE -->

            <div class="form-group">

                <label>Date</label>

                <input
                    type="date"
                    name="date"
                    value="<?= htmlspecialchars($row['date'] ?? '') ?>"
                    required
                >

            </div>


            <!-- POWDER -->

            <div class="form-group">

                <label>Qty Powder (kg)</label>

                <input
                    type="number"
                    step="0.01"
                    name="output_powder"
                    value="<?= htmlspecialchars($row['output_powder'] ?? '') ?>"
                    required
                >

            </div>


            <!-- SCRAP -->

            <div class="form-group">

                <label>Qty Scrap (kg)</label>

                <input
                    type="number"
                    step="0.01"
                    name="qty_scarp"
                    value="<?= htmlspecialchars($row['qty_scarp'] ?? '') ?>"
                    required
                >

            </div>


            <!-- BUTTON -->

            <div class="button-area">

                <a
                    href="record.php"
                    class="back-btn"
                >
                    Back
                </a>

                <button
                    type="submit"
                    class="update-btn"
                >
                    Update
                </button>

            </div>


        </form>

    </div>

</div>

</body>

</html>