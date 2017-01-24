<?php
/**
 * CONTROLLER
 */
    require("vendor/autoload.php");
    require("model/index.php");
    require("model/assignments.php");
    require("model/submissions.php");
    require("model/login.php");
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

    function getRedirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    function getBootstrap(){
        return new Bootstrap;
    }

    $router = new \Bramus\Router\Router();

    $router->before('GET|POST', '/assignment/.*', function() {
        session_start();
        if (!isset($_SESSION['user'])) {
            getRedirect("/login");
            exit();
        }
    });

    $router->get("/", function(){
        getRedirect("/login");
    });

    $router->get("/login/", function (){
        echo getTemplates()->render("login::login", ["title" => "Hofstad | Inloggen"]);
    });

    $router->post("/login/", function (){
        $db = getDatabase();
       if(check_login($db, $_POST['username'], $_POST['password'])){
           session_start();
           $_SESSION['user'] = $_POST['username'];
           $userinfo = getUserInfo($db, $_POST['username']);
           $_SESSION['class'] = $userinfo["class"];
           $_SESSION['name'] = $userinfo["name"];
           getRedirect("/assignment/");
       } else {
           getRedirect("/login/?failed=true");
       }
    });

    $router->get('/logout/', function (){
            session_start();
            session_destroy();
            getRedirect("/login/?logged_out=true");
        });

    $router->get("/assignment/", function (){
                $bp = getBootstrap();
                session_start();

                // Get data
                $data = getAssignments(getDatabase(), $_SESSION['user']);
                $columns = [["Titel", "title"], ["Status", "status"], ["Uiterste inleverdatum", "end_date"]];

                // Generate menu
                $menu = generateMenu($bp, ["active" => "Opdrachten", "align" => "stacked"]);
                $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["name"] => "#", "Opdrachten" => "#"]);
                $link = '<a href="%s/">%s</a>';

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
        $database = getDatabase();
        // Generate menu
        $menu = generateMenu($bp, ["active" => "Opdrachten", "align" => "stacked"]);
        $data = getAssignment($database, $assignment_id, $_SESSION["class"]);
        $submission = getSubmission($database, $_SESSION["user"], $assignment_id);

        $breadcrumbs = generateBreadcrumbs($bp, ["L&eacute;on Melein" => "#", "Opdrachten" => "../", $data['title'] => "#"]);
        $tabs = generateTabs($bp);

        $page_js = "";
        $overwrite = 0;
        if($submission != null){
            $page_js = "$('a[href=\"#submission\"]').tab('show');
            $(\"#inzendingoverschrijven\").click(function (e) {
            $(\"#togglealert\").removeClass(\"show\");
            $(\"#togglealert\").addClass(\"hide\");
            $(\"#alertshow\").removeClass(\"hide\");
            $(\"#alertshow\").addClass(\"show\");
            });
            
            $(\"#gaverder\").click(function (e) {
            $(\"#alertshow\").removeClass(\"show\");
            $(\"#alertshow\").addClass(\"hide\");
            $(\"#fromoverschrijven\").removeClass(\"hide\");
            $(\"#fromoverschrijven\").addClass(\"show\");
            });";
            $overwrite = 1;
        }

        echo getTemplates()->render("assignments::assignment", ["title" => sprintf("Hofstad | Opdracht: %s", strtolower($data['title'])),
                                                                "breadcrumbs" => $breadcrumbs,
                                                                "menu" => $menu,
                                                                "page_title" => $data['title'],
                                                                "status" => $data['status'],
                                                                "deadline" => $data["deadline"],
                                                                "submission" => $submission,
                                                                "tabs" => $tabs,
                                                                "page_js" => $page_js,
                                                                "overwrite" => $overwrite
        ]);
    });

    $router->post("/assignment/(\d+)/submit", function ($assignment_id){
        session_start();
        $db = getDatabase();

        $previous_submission = getSubmissionFile($db, $_SESSION["user"], $assignment_id);

        $storage = new \Upload\Storage\FileSystem('/volume1/hofstad/assets/submissions/');
        $file = new \Upload\File('file', $storage);
        $new_filename = uniqid();
        $db_filename = $new_filename . "." . $file->getExtension();

        $originalfilename = $file->getNameWithExtension();

        $file->setName($new_filename);

        $file->addValidations(array(
            // Ensure file is of type "image/png"
            new \Upload\Validation\Mimetype(array('application/vnd.openxmlformats-officedocument.wordprocessingml.document')),

            // Ensure file is no larger than 5M (use "B", "K", M", or "G")
            new \Upload\Validation\Size('5M')
        ));

        // Try to upload file
        try {
            // Success!
            $file->upload();
            if (strlen($previous_submission) > 5){
                $rows_affected = updateSubmission(getDatabase(), $_SESSION['user'], $assignment_id, $originalfilename, $db_filename, $previous_submission);
            } else {
                $rows_affected = setSubmission(getDatabase(), $_SESSION['user'], $assignment_id, $originalfilename, $db_filename);
            }


            if ($rows_affected){
                getRedirect("/assignment/".$assignment_id."/?upload=success");
            } else {
                getRedirect("/assignment/".$assignment_id."/?upload=failed");
            }
        } catch (\Exception $e) {
            getRedirect("/assignment/".$assignment_id."/?upload=failed");
        }
    });

    $router->get("/register/", function (){
        echo getTemplates()->render("login::register", ["title" => "Hofstad | Registreren"]);
    });

    $router->post("/register/", function(){
        $db = getDatabase();
        if(set_initial_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/login/?registration=true");
        } else {
            getRedirect("/register/?failed=true");
        }
    });

    $router->run();