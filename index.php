<?php
    /**
     * 
     */
    require_once 'controllers/TestController.php';
    session_start();
    if (!isset($_SESSION['controller'])){
        $_SESSION['controller'] = new TestController();
    }
    echo $_SESSION['controller']->handleRequest();
?>