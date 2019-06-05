<!DOCTYPE html>
<html>
    <head>
        <title>Test application</title>
        <?php require_once "views/bootstrap.php" ?>
        <link rel = "stylesheet" type="text/css" href="resources/styles/startSession.css" />
    </head>
    <body>
        <h1>Testa uzdevums</h1>
        <?php
            if (is_string($this->error) && strlen($this->error) > 0){
                echo '<p class = "error">'.$this->error.'</p>';
                $this->error = "";
            }
        ?>
        <form method = "post">
            <input type="text" class="inp" id="username" placeholder="Ievadi savu vārdu" name="username">
            <select class="inp" id="test" name="test_selection">
                <option value="0">Izvēlies testu</option>
                <?php  
                    $test_list = $conn->get("SELECT * FROM test");
                    foreach ($test_list as $test){
                        echo '<option value="'.$test['test_id'].'">';
                        echo $test['test_title'];
                        echo '</option>';
                    }
                ?>
            </select>
            <input class="btn" type="submit" value="Sākt" name="begin" id="begin">
        </form>
    <script src="resources/scripts/inputValidation.js"></script>
    <script src="resources/scripts/utils.js"></script>
    </body>


</html>