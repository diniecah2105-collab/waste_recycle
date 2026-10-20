<?php

session_start();

include "db.php";

$error = "";


/*
=========================================================
LOGIN PROCESS
=========================================================
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Employee ID daripada form
    $employee_id = trim($_POST['username'] ?? '');

    // Password daripada form
    $password = trim($_POST['password'] ?? '');


    /*
    =====================================================
    CHECK INPUT
    =====================================================
    */

    if ($employee_id == "" || $password == "") {

        $error = "Please enter Employee ID and Password.";

    } else {


        /*
        =================================================
        CHECK ADMIN LOGIN TABLE
        =================================================
        */

        $sql = "SELECT *
                FROM admin_login
                WHERE employee_id = ?
                LIMIT 1";


        $stmt = mysqli_prepare($conn, $sql);


        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $employee_id
            );

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);


            /*
            =============================================
            LOGIN FOUND
            =============================================
            */

            if ($result && mysqli_num_rows($result) == 1) {

                $admin = mysqli_fetch_assoc($result);


                /*
                =========================================
                CHECK PASSWORD
                =========================================

                Buat masa sekarang password dalam
                database masih plaintext.
                =========================================
                */

                if ($password === $admin['password']) {


                    /*
                    =====================================
                    CREATE SESSION
                    =====================================
                    */

                    $_SESSION['admin'] = $admin['employee_id'];

                    $_SESSION['employee_id'] = $admin['employee_id'];

                    $_SESSION['admin_login'] = true;


                    /*
                    =====================================
                    GO TO ADMIN DASHBOARD
                    =====================================
                    */

                    header("Location: admin/dashboard.php");

                    exit;


                } else {

                    $error = "Invalid username or password.";

                }


            } else {

                $error = "Invalid username or password.";

            }


            mysqli_stmt_close($stmt);


        } else {

            $error = "Database query error.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Waste Recycle System</title>


    <!-- =========================
         CSS
    ========================== -->

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        /* =========================
           BODY
        ========================== */

        body {
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background-image: url("img/background_login.png");

            background-size: cover;

            background-position: center;

            background-repeat: no-repeat;
        }


        /* =========================
           LOGIN CONTAINER
        ========================== */

        .login-container {

            width: 420px;

            background: #ffffffee;

            padding: 40px;

            border-radius: 15px;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);

            text-align: center;
        }


        /* =========================
           LOGO
        ========================== */

        .logo {
            margin-bottom: 20px;
        }


        .logo img {

            width: 90px;

            height: auto;
        }


        /* =========================
           TITLE
        ========================== */

        .title {

            font-size: 22px;

            font-weight: bold;

            color: #333;

            margin-bottom: 30px;
        }


        /* =========================
           INPUT GROUP
        ========================== */

        .input-group {

            text-align: left;

            margin-bottom: 18px;
        }


        /* LABEL */

        .input-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 15px;

            color: #333;
        }


        /* INPUT */

        .input-group input {

            width: 100%;

            padding: 13px;

            border: 1px solid #ccc;

            border-radius: 7px;

            font-size: 15px;
        }


        /* INPUT FOCUS */

        .input-group input:focus {

            outline: none;

            border-color: #008B8B;
        }


        /* =========================
           LOGIN BUTTON
        ========================== */

        .login-btn {

            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 7px;

            background: #1929bb;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;
        }


        .login-btn:hover {

            background: #142099;
        }


        /* =========================
           OR
        ========================== */

        .or {

            margin: 25px 0;

            color: #6f6c6c;

            font-size: 14px;
        }


        /* =========================
           OPERATOR BUTTON
        ========================== */

        .operator-btn {

            display: block;

            width: 100%;

            padding: 13px;

            background: #35dd5c;

            color: white;

            text-decoration: none;

            border-radius: 7px;

            font-size: 15px;

            font-weight: bold;
        }


        .operator-btn:hover {

            background: #28c94e;
        }


        /* =========================
           ERROR
        ========================== */

        .error {

            background: #ffe5e5;

            color: #cc0000;

            padding: 10px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 14px;
        }


        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 500px) {

            .login-container {

                width: 90%;

                padding: 30px 25px;
            }

        }

    </style>

</head>


<body>


    <!-- =========================
         LOGIN CONTAINER
    ========================== -->

    <div class="login-container">


        <!-- =========================
             LOGO
        ========================== -->

        <div class="logo">

            <img
                src="img/LogoNichias.jpg"
                alt="NICHIAS Logo"
            >

        </div>


        <!-- =========================
             TITLE
        ========================== -->

        <div class="title">

            WASTE RECYCLE SYSTEM

        </div>


        <!-- =========================
             ERROR MESSAGE
        ========================== -->

        <?php if ($error != "") { ?>

            <div class="error">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php } ?>


        <!-- =========================
             LOGIN FORM
        ========================== -->

        <form method="POST">


            <!-- EMPLOYEE ID -->

            <div class="input-group">

                <label>
                    Employee ID
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="e.g 001234"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="input-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >

            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-btn"
            >

                LOGIN

            </button>


        </form>


        <!-- =========================
             OR
        ========================== -->

        <div class="or">

            ───── OR ─────

        </div>


        <!-- =========================
             OPERATOR
        ========================== -->

        <a
            href="operator/form.php"
            class="operator-btn"
        >

            ENTER AS OPERATOR

        </a>


    </div>


</body>

</html>