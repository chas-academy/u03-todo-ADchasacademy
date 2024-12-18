<?php
$dsn = 'mysql:host=mariadb;dbname=kanban_board';
$username = 'root';
$password = 'mariadb';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$Users = $pdo->query("SELECT * FROM Users")->fetchAll();
$Priority = $pdo->query("SELECT * FROM Priority")->fetchAll();
$ColumnTable = $pdo->query("SELECT * FROM ColumnTable")->fetchAll();
$Task = $pdo->query("SELECT * FROM Task")->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TaskName']) && isset($_POST['ColumnName']) && isset($_POST['Description']) && isset($_POST['PriorityName']) && isset($_POST['UserName'])) {

    $ok = true;
    $TaskName = trim(filter_var($_POST['TaskName'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $ColumnName = trim(filter_var($_POST['ColumnName'], FILTER_SANITIZE_NUMBER_INT));
    $Description = trim(filter_var($_POST['Description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $PriorityName = trim(filter_var($_POST['PriorityName'], FILTER_SANITIZE_NUMBER_INT));
    $UserName = trim(filter_var($_POST['UserName'], FILTER_SANITIZE_NUMBER_INT));

    if ($TaskName === '' || $ColumnName === '' || $UserName === '' || $Description === '' || $PriorityName === '') {
        $ok = false;
    }

    if ($ok) {
        $sql = "INSERT INTO Task (TaskName, ColumnID, Description, PriorityID, UserID) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$TaskName, $ColumnName, $Description, $PriorityName, $UserName]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $ToBeDeleted = trim(filter_var($_POST['delete'], FILTER_SANITIZE_NUMBER_INT));
    $sql = "DELETE FROM Task WHERE TaskID = (?)";
    $pdo->prepare($sql)->execute([$ToBeDeleted]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['EditTask']) && isset($_POST['testing'])) {
    $ok = true;
    $EditTaskName = trim(filter_var($_POST['EditTask'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $Description = trim(filter_var($_POST['testing'], FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $TaskID = $_POST['TaskID'];

    // kika på empty funktionen

    if ($EditTaskName === '' || $Description === '' || $TaskID === '') {
        $ok = false;
    }

    if ($ok) {
        $sql = "UPDATE Task SET TaskName = :TaskName, Description = :Description WHERE TaskID = :TaskID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':TaskName' => $EditTaskName,
            ':Description' => $Description,
            ':TaskID' => $TaskID
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TaskID'])  && isset($_POST['CurrentValue']) && isset($_POST['LevelUPDown'])) {


    $TaskID = $_POST['TaskID'];
    $CurrentValue = $_POST['CurrentValue'];

    if ($_POST['LevelUPDown'] === '0') {
        $NewValue = $CurrentValue + 1;
    } else {
        $NewValue = $CurrentValue - 1;
    }

    if ($NewValue < 5 && $NewValue > 0) {


        $sql = "UPDATE Task SET ColumnID = :ColumnID WHERE TaskID = :TaskID";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':ColumnID' => $NewValue,
            ':TaskID' => $TaskID
        ]);
    }
}

// Ändra, lägg till för varje funktion istället?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="./css/styles.css" />
    <title>Google Innovation Kanban Board</title>
</head>


<!-- kika på stackoverflow -->

<body>
    <main>
        <form
            action=""
            method="POST">
            <input type="text" placeholder="Enter text" name="TaskName">
            <textarea placeholder="Enter a description" name="Description"></textarea>

            <select name="ColumnName">
                <?php
                foreach ($ColumnTable as $row) {
                    echo "<option value='" . $row['ColumnID'] . "'>" . $row['ColumnName'] . "</option>";
                }
                ?>
            </select>
            <select name="PriorityName">

                <?php
                foreach ($Priority as $row) {
                    echo "<option value='" . $row['PriorityID'] . "'>" . $row['PriorityName'] . "</option>";
                }
                ?>
            </select>
            <select name="UserName">
                <?php
                foreach ($Users as $row) {
                    echo "<option value='" . $row['UserID'] . "'>" . $row['UserName'] . "</option>";
                }
                ?>
            </select>
            <input type="submit">
        </form>

        <section>
            <h1 id="main-title">
                Innovation Pipeline: Driving Google's Next Big Leap
            </h1>
        </section>

        <div class="board-container">
            <section class="column-container">
                <h2 class="section-title">To do</h2>
                <div class="section-line-r"></div>
                <div class="task-wrapper">

                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  1) {
                    ?>
                            <article>
                                <div class="task-container">
                                    <div class="icon-styling">
                                        <form action='' method='POST'>
                                            <input type='hidden' value=" <?php echo $TaskDesc['TaskID']; ?>" name='delete'>
                                            <button type="submit" class="remove">x</button>
                                        </form>
                                    </div>
                                    <div class="task-info">
                                        <h3 class="task-title"><?php echo $TaskDesc['TaskName'] ?></h3>
                                        <p class="txt-desc"><?php echo $TaskDesc['Description'] ?></p>
                                    </div>
                                </div>
                                <div class="testing">

                                    <button type="button" disabled class="hidden"></button>
                                    <form action='' method='POST' class="edit-wrap">
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?> " name='TaskID'>
                                        <input placeholder='Edit Title' type='text' name='EditTask' class="border">
                                        <input name='testing' placeholder='Enter Description' class="border">
                                        <button type="submit" class="edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                <path
                                                    fill="#00000080"
                                                    d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                            </svg>
                                        </button>
                                    </form>

                                    <form action='' method='POST'>
                                        <input type='hidden' value='0' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-up-in-progress">></button>
                                    </form>
                                </div>

                            </article>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
            <section class="column-container">
                <h2 class="section-title">In Progress</h2>
                <div class="section-line-y"></div>
                <div class="task-wrapper">

                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  2) {
                    ?>
                            <article>
                                <div class="task-container">
                                    <div class="icon-styling">
                                        <form action='' method='POST'>
                                            <input type='hidden' value=" <?php echo $TaskDesc['TaskID']; ?>" name='delete'>
                                            <button type="submit" class="remove">x</button>
                                        </form>
                                    </div>
                                    <div class="task-info">
                                        <h3 class="task-title"><?php echo $TaskDesc['TaskName'] ?></h3>
                                        <p class="txt-desc"><?php echo $TaskDesc['Description'] ?></p>
                                    </div>
                                </div>
                                <div class="testing">
                                    <form action='' method='POST'>
                                        <input type='hidden' value='1' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-down-to-do">
                                            < </button>
                                    </form>
                                    <button type="submit" class="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path
                                                fill="#00000080"
                                                d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>
                                    </button>
                                    <form action='' method='POST'>
                                        <input type='hidden' value='0' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-up-done">></button>
                                    </form>
                                </div>
                            </article>
                    <?php

                        }
                    }
                    ?>
                </div>
            </section>

            <section class="column-container">
                <h2 class="section-title">Done</h2>
                <div class="section-line-b"></div>
                <div class="task-wrapper">

                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  3) {
                    ?>
                            <article>
                                <div class="task-container">
                                    <div class="icon-styling">
                                        <form action='' method='POST'>
                                            <input type='hidden' value=" <?php echo $TaskDesc['TaskID']; ?>" name='delete'>
                                            <button type="submit" class="remove">x</button>
                                        </form>
                                    </div>
                                    <div class="task-info">
                                        <h3 class="task-title"><?php echo $TaskDesc['TaskName'] ?></h3>
                                        <p class="txt-desc"><?php echo $TaskDesc['Description'] ?></p>
                                    </div>
                                </div>
                                <div class="testing">
                                    <form action='' method='POST'>
                                        <input type='hidden' value='1' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-down-in-progress">
                                            < </button>
                                    </form>
                                    <button type="submit" class="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path
                                                fill="#00000080"
                                                d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>
                                    </button>
                                    <form action='' method='POST'>
                                        <input type='hidden' value='0' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-up-approved">></button>
                                    </form>
                                </div>
                            </article>
                    <?php
                        }
                    }
                    ?>
                </div>
            </section>
            <section class="column-container">
                <h2 class="section-title">Approved</h2>
                <div class="section-line-g"></div>
                <div class="task-wrapper">

                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  4) {
                    ?>
                            <article>
                                <div class="task-container">
                                    <div class="icon-styling">
                                        <form action='' method='POST'>
                                            <input type='hidden' value=" <?php echo $TaskDesc['TaskID']; ?>" name='delete'>
                                            <button type="submit" class="remove">x</button>
                                        </form>
                                    </div>
                                    <div class="task-info">
                                        <h3 class="task-title"><?php echo $TaskDesc['TaskName'] ?></h3>
                                        <p class="txt-desc"><?php echo $TaskDesc['Description'] ?></p>
                                    </div>
                                </div>
                                <div class="testing">
                                    <form action='' method='POST'>
                                        <input type='hidden' value='1' name='LevelUPDown'>
                                        <input type='hidden' value="<?php echo $TaskDesc['TaskID']; ?>" name='TaskID'>
                                        <input type='hidden' value="<?php echo $TaskDesc['ColumnID']; ?>" name='CurrentValue'>
                                        <button type="submit" class="move-down-done">
                                            < </button>
                                    </form>
                                    <button type="submit" class="edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                            <path
                                                fill="#00000080"
                                                d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>
                                    </button>
                                    <button type="button" disabled class="hidden"></button>
                                </div>
                            </article>
                    <?php
                        }
                    }
                    ?>

                </div>
            </section>

        </div>
    </main>
    <footer></footer>
</body>

</html>