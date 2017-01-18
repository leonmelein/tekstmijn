<?php
/**
 * CONTROLLER
 */
    require("vendor/autoload.php");
    require("model/index.php");
    require("model/assignments.php");
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
        $templates->addFolder("assignments", "view/assignments");
        return $templates;
    }

    function getBootstrap(){
        return new Bootstrap;
    }

    $router = new \Bramus\Router\Router();

    $router->get("/", function (){
        $bp = getBootstrap();
        session_start();
        $_SESSION['user'] = 16001;
        $_SESSION['class'] = 1;

        // Get data
        $data = getAssignments(getDatabase(), 16001);
        $columns = [["Titel", "title"], ["Status", "status"], ["Uiterste inleverdatum", "end_date"]];

        // Generate menu
        $menu = generateMenu($bp, ["active" => "Opdrachten", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, ["L&eacute;on Melein" => "#", "Opdrachten" => "#"]);
        $link = '<a href="assignment/%s/">%s</a>';

        // Generate page
        echo getTemplates()->render("assignments::index", [
            "title" => "Hofstad | Overzicht",
            "page_title" => "Opdrachten",
            "table" => generateTable($bp, $columns, $data, $link),
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
        ]);
    });

    $router->get("/assignment/(\d+)", function ($assignment_id) {
        session_start();

        $bp = getBootstrap();
        // Generate menu
        $menu = generateMenu($bp, ["active" => "Opdrachten", "align" => "stacked"]);
        $data = getAssignment(getDatabase(), $assignment_id);
        $breadcrumbs = generateBreadcrumbs($bp, ["L&eacute;on Melein" => "#", "Opdrachten" => "../../", "Opdracht" => "#"]);

        echo getTemplates()->render("assignments::assignment", ["title" => "Hofstad | Opdrachten", "breadcrumbs" => $breadcrumbs,
            "menu" => $menu, "page_title" => $data['title'], "status" => $data['status'], "deadline" => $data["deadline"]
        ]);
    });

    $router->run();