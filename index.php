<?php
/**
 * CONTROLLER
 */
    require("vendor/autoload.php");

    $database = new medoo([
        'database_type' => 'mysql',
        'database_name' => 'hofstad',
        'server' => 'localhost',
        'username' => 'hofstad',
        'password' => 'LR_hdh4@26', // TODO: Move to config file?
        'charset' => 'utf8'
    ]);
    $templates = new League\Plates\Engine('view', 'tpl');
    $router = new \Bramus\Router\Router();

    $router->get("/", function (){
        echo "<h1>Hello world!</h1>";
    });

    $router->run();