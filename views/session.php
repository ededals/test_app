
<!DOCTYPE html>
<html>
    <head>
        <title>Test application</title>
        <?php require_once "views/bootstrap.php" ?>
        <link rel = "stylesheet" type="text/css" href="resources/styles/session.css" />

    </head>
    <body>
        <h1 id="question">
            <?php
                echo $this->test_session->get_current_question();
            ?>
        </h1>
        <?php
            if (is_string($this->error) && strlen($this->error) > 0){
                echo '<p class = "error">'.$this->error.'</p>';
                $this->error = "";
            }
        ?>
        <form method="post">
            <ul id="answer-list">
                <?php
                    $answers = $this->test_session->get_answers();
                    foreach ($answers as $k=>$v){
                        echo '<li>';
                        echo '<input type="radio" class="answer" name="answer" value="'.$k.'" id="'.$k.'">';
                        echo '<label for="'.$k.'">'.$v.'</label>';
                        echo '</li>';
                    }
                ?>
            </ul>
            <div id="progress-container">
                <div id="progress-marker" style="width:<?php 
                    $width = $this->test_session->get_session_progress();
                    echo $width."%";
                ?>"></div>
            </div>
            <input type="submit" id="next" class= "btn" name="next" value="Next">
        </form>
        <script src="resources/scripts/nextClickHandle.js"></script>
        <script src="resources/scripts/utils.js"></script>

    </body>
</html>
