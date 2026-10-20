<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Waste Recycling System</title>

<style>

body {
    font-family: Arial, sans-serif;
    background-color: #E0FFFF;
    height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    margin: 0;
}

/* FRAME UTAMA */
.container {
    width: 450px;
    background: white;

    padding: 40px 50px;

    border-radius: 15px;

    text-align: center;

    box-shadow: 0px 5px 15px rgba(0,0,0,0.2);

    border: 2px solid #008B8B;
}

/* TITLE */
h1 {
    color: #00BFFF;

    margin-top: 0;
    margin-bottom: 35px;

    font-size: 30px;
}

/* BUTTON */
.menu-button {
    display: block;

    width: 250px;

    padding: 15px;

    margin: 15px auto;

    box-sizing: border-box;

    font-size: 18px;

    text-align: center;

    text-decoration: none;

    border-radius: 8px;

    color: white;

    transition: 0.2s;

    border: none;

    cursor: pointer;
}

/* MAIN MENU */
.main-btn {
    background-color: #008B8B;
}

.main-btn:hover {
    background-color: #006666;
}

/* RECORD OPERATOR */
.operator-btn {
    background-color: #008B8B;
}

.operator-btn:hover {
    background-color: #006666;
}

/* BACK BUTTON */
.back-btn {
    background-color: #808080;

    width: 150px;

    padding: 10px;

    margin-top: 30px;
}

.back-btn:hover {
    background-color: #666666;
}

</style>

</head>

<body>

<div class="container">

    <h1>MAIN MENU</h1>

    <a href="main.php" class="menu-button main-btn">
        Main          
    </a>

    <a href="operator_record.php" class="menu-button operator-btn">
        Record Operator
    </a>

    <button onclick="history.back()" class="menu-button back-btn">
        Back
    </button>

</div>

</body>

</html>