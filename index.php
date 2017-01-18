<?php
/**
 * CONTROLLER
 */
    require("vendor/autoload.php");
    require("model/index.php");
    use BootPress\Bootstrap\v3\Component as Bootstrap;

    function getDatabase(){
        $database = new medoo([
            'database_type' => 'mysql',
            'database_name' => 'hofstad',
            'server' => 'srv-01.reinardvandalen.nl',
            'username' => 'hofstad',
            'password' => 'LR_hdh4@26', // TODO: Move to config file?
            'charset' => 'utf8'
        ]);
        return $database;
    }

    function getTemplates(){
        $templates = new League\Plates\Engine('view', 'tpl');
        $templates->addFolder("login", "view/login");
        return $templates;
    }

    function getBootstrap(){
        return new Bootstrap;
    }

    $router = new \Bramus\Router\Router();

    $router->get("/", function (){
        // Get data
        $data = getStudents(getDatabase());
        $columns = [["#", "id"], ["First name", "firstname"], ["Last name", "lastname"]];

        // Generate page
        echo getTemplates()->render("overview", [
            "title" => "Hofstad - Overzicht",
            "table" => generateTable(getBootstrap(), $columns, $data)
        ]);
    });

    $router->get("/signin", function (){
        echo getTemplates()->render("login::login", ["title" => "Hofstad - Login"]);
    });

    $router->run();