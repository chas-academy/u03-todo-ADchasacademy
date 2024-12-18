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
    $TaskName = trim(filter_var($_POST['TaskName'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $ColumnName = trim(filter_var($_POST['ColumnName'] ?? '', FILTER_SANITIZE_NUMBER_INT));
    $Description = trim(filter_var($_POST['Description'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $PriorityName = trim(filter_var($_POST['PriorityName'] ?? '', FILTER_SANITIZE_NUMBER_INT));
    $UserName = trim(filter_var($_POST['UserName'] ?? '', FILTER_SANITIZE_NUMBER_INT));

    if ($TaskName === '' || $ColumnName === '' || $UserName === '' || $Description === '' || $PriorityName === '') {
        $ok = false;
    }

    if ($ok) {
        $sql = "INSERT INTO Task (TaskName, ColumnID, Description, PriorityID, UserID) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$TaskName, $ColumnName, $Description, $PriorityName, $UserName]);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    $ToBeDeleted = trim(filter_var($_POST['delete'] ?? '', FILTER_SANITIZE_NUMBER_INT));
    $sql = "DELETE FROM Task WHERE TaskID = (?)";
    $pdo->prepare($sql)->execute([$ToBeDeleted]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['EditTask']) && isset($_POST['testing'])) {
    $ok = true;
    $EditTaskName = trim(filter_var($_POST['EditTask'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $Description = trim(filter_var($_POST['testing'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
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
    <title>Kanban</title>
</head>


<!-- kika på stackoverflow -->

<body>
    <main>
        <form
            action=""
            method="POST">
            <input type="text" placeholder="Enter text" name="TaskName">
            <input type="textarea" placeholder="Enter a description" name="Description">

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

        <section class="container">
            <h1>KANBAN BOARD</h1>
        </section>

        <div class="wrapper">
            <section>
                <h2 class="container">TO DO</h2>
                <div class="container">

                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  1) {
                    ?>
                            <article>
                                <h3 class="task-title"><?php echo $TaskDesc['TaskName'] ?></h3>
                                <p class="txt-desc"><?php echo $TaskDesc['Description'] ?></p>
                            </article>
                    <?php



                            //     echo "<article>" . $TaskDesc['TaskName'] . ' ' . $TaskDesc['Description'] . "<form action='' method='POST' > <input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='delete'><button type='submit'>Remove</button> </form><form action='' method='POST' ><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'> <input placeholder='Edit Title'type='text' name='EditTask'> <input name='testing' placeholder='Enter Description'> <button type='submit'>Edit</button></form> 
                            //     <form action='' method='POST' > <input type='hidden' value='0' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>Up</button> </form>
                            //     <form action='' method='POST' > <input type='hidden' value='1' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>down</button> </form> </article>";
                        }
                    }
                    ?>
                </div>
            </section>
            <section>
                <h2 class="container">IN PROGRESS</h2>
                <div class="container">
                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  2) {
                            echo "<article>" . $TaskDesc['TaskName'] . ' ' . $TaskDesc['Description'] . "<form action='' method='POST' > <input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='delete'><button type='submit'>Remove</button> </form><form action='' method='POST' ><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'> <input placeholder='Edit Title'type='text' name='EditTask'> <input name='testing' placeholder='Enter Description'> <button type='submit'>Edit</button></form> 
                            <form action='' method='POST' > <input type='hidden' value='0' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>Up</button> </form>
                            <form action='' method='POST' > <input type='hidden' value='1' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>down</button> </form> </article>";
                        }
                    }
                    ?>

                </div>
            </section>
            <section>
                <h2 class="container">DONE</h2>
                <div class="container">
                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  3) {
                            echo "<article>" . $TaskDesc['TaskName'] . ' ' . $TaskDesc['Description'] . "<form action='' method='POST' > <input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='delete'><button type='submit'>Remove</button> </form><form action='' method='POST' ><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'> <input placeholder='Edit Title'type='text' name='EditTask'> <input name='testing' placeholder='Enter Description'> <button type='submit'>Edit</button></form> 
                            <form action='' method='POST' > <input type='hidden' value='0' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>Up</button> </form>
                            <form action='' method='POST' > <input type='hidden' value='1' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>down</button> </form> </article>";
                        }
                    }
                    ?>

                </div>
            </section>
            <section>
                <h2 class="container">APPROVED</h2>
                <div class="container">
                    <?php
                    foreach ($Task as $TaskDesc) {
                        if ($TaskDesc['ColumnID'] ===  4) {
                            echo "<article>" . $TaskDesc['TaskName'] . ' ' . $TaskDesc['Description'] . "<form action='' method='POST' > <input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='delete'><button type='submit'>Remove</button> </form><form action='' method='POST' ><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'> <input placeholder='Edit Title'type='text' name='EditTask'> <input name='testing' placeholder='Enter Description'> <button type='submit'>Edit</button></form> 
                            <form action='' method='POST' > <input type='hidden' value='0' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>Up</button> </form>
                            <form action='' method='POST' > <input type='hidden' value='1' name='LevelUPDown'><input type='hidden' value='" . $TaskDesc['TaskID'] . "' name='TaskID'><input type='hidden' value='" . $TaskDesc['ColumnID'] . "' name='CurrentValue'><button type='submit'>down</button> </form> </article>";
                        }
                    }
                    ?>

                </div>
            </section>
        </div>
        </div>
    </main>
</body>

</html>