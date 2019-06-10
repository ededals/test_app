<?php
    require_once "models/Connection.php";
    require_once 'controllers/TestController.php';
    $conn = new Connection();
    $controller = new TestController($conn);
    $controller->handleRequest();
?>