<?php
/**
 * CONTROLLER
 */
    require("vendor/autoload.php");

    $database = new medoo([
        'database_type' => 'mysql',
        'database_name' => 'hofstad',
        'server' => 'srv-01.reinardvandalen.nl',
        'username' => 'hofstad',
        'password' => 'LR_hdh4@26', // TODO: Move to config file?
        'charset' => 'utf8'
    ]);

    function getTemplates(){
        $templates = new League\Plates\Engine('view', 'tpl');
        //$templates->addFolder("assignments", "/view/assignments");
        //$templates->addFolder("questionnaires", "/view/questionnaires");
        //$templates->addFolder("signup", "/view/signup");
        return $templates;
    }

    $router = new \Bramus\Router\Router();

    $router->get("/", function (){
        echo getTemplates()->render("overview", ["title" => "Hofstad"]);
    });

    $router->run();