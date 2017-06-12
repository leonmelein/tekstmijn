<?php
    /**
     * CONTROLLER
     */
    require("vendor/autoload.php");
    require("model/index.php");
    require("model/assignments.php");
    require("model/submissions.php");
    require("model/login.php");
    require("model/questionnaires.php");
    use BootPress\Bootstrap\v3\Component as Bootstrap;
    use Medoo\Medoo as medoo;

    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if(!headers_sent()) {
            header("Status: 301 Moved Permanently");
            header(sprintf(
                'Location: https://%s%s',
                $_SERVER['HTTP_HOST'],
                $_SERVER['REQUEST_URI']
            ));
            exit();
        }
    }

    function getDatabase() {
        $db_settings = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/config/config.ini");
        $database = new Medoo([
            'database_type' => 'mysql',
            'database_name' => $db_settings['database_name'],
            'server' => $db_settings['server'],
            'username' => $db_settings['username'],
            'password' => $db_settings['password'],
            'charset' => 'utf8'
        ]);
        return $database;
    }

    function getTemplates(){
        $templates = new League\Plates\Engine('view', 'tpl');
        $templates->addFolder("login", "view/login");
        $templates->addFolder("assignments", "view/assignments");
        $templates->addFolder("questionnaires", "view/questionnaires");
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


    /*
     * Authentication
     * - Provides routes for logging on and off, as well as registering a new account.
     */

    //  Authentication check: check if each request has a user ID set in session.
    //  TODO: use tokens?
    $router->before('GET|POST', '/assignment/.*', function() {
        auth_check();
    });

    $router->before('GET|POST', '/assignment/', function() {
        auth_check();
    });

    $router->before('GET|POST', '/questionnaire/.*', function() {
        auth_check();
    });

    $router->before('GET|POST', '/questionnaire/', function() {
        auth_check();
    });

    $router->get("/", function(){
            getRedirect("/login");
    });

    $router->get("/login/", function (){
        echo getTemplates()->render("login::login", ["title" => "Tekstmijn | Inloggen"]);
    });

    $router->post("/login/", function () {
        $db = getDatabase();
       if(check_login($db, $_POST['username'], $_POST['password'])){
           session_start();
           $userinfo = getUserInfo($db, $_POST['username']);
           $_SESSION['user'] = $_POST['username'];
           $_SESSION['class'] = $userinfo["class"];
           $_SESSION['school'] = $userinfo["school"];
           $_SESSION['name'] = $userinfo["name"];
           $_SESSION['id'] = $userinfo["id"];
           getRedirect("/questionnaire/");
       } else {
           getRedirect("/login/?failed=true");
       }
    });

    $router->get('/logout/', function (){
            session_start();
            session_destroy();
            getRedirect("/login/?logged_out=true");
        });

    $router->get("/register/", function (){
        echo getTemplates()->render("login::register", ["title" => "Tekstmijn | Registreren"]);
    });

    $router->post("/register/", function(){
        $db = getDatabase();
        if(set_initial_password($db, $_POST["username"], $_POST["password"])){
            getRedirect("/login/?registration=true");
        } else {
            getRedirect("/register/?failed=true");
        }
    });

    /*
     * Assignments
     *      Provides access to assignments and enables submission.
     */

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
                $options = [
                    ["<a class='' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Inleveren</a>"],
                ];

                // Generate page
                echo getTemplates()->render("assignments::index", [
                    "title" => "Tekstmijn | Opdrachten",
                    "page_title" => "Opdrachten",
                    "table" => generateTable($bp, $columns, $data, $options, $link),
                    "menu" => $menu,
                    "breadcrumbs" => $breadcrumbs,
                ]);
            });

    $router->get("/assignment/(.*)/", function ($assignment_id) {
        session_start();
        $bp = getBootstrap();
        $database = getDatabase();
        // Generate menu
        $menu = generateMenu($bp, ["active" => "Opdrachten", "align" => "stacked"]);
        $data = getAssignment($database, $assignment_id, $_SESSION["class"]);
        $submission = getSubmission($database, $_SESSION["user"], $assignment_id);

        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["name"] => "#", "Opdrachten" => "../", $data['title'] => "#"]);
        $tabs = generateTabs($bp);

        $page_js = "";
        $overwrite = 0;
        if($submission != null){
            $page_js = "/vendor/application/assignment_submitted.js";
            $overwrite = 1;
        }

        echo getTemplates()->render("assignments::assignment", ["title" => sprintf("Tekstmijn | Opdracht: %s", strtolower($data['title'])),
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

    $router->post("/assignment/(.*)/submit", function ($assignment_id){
        session_start();
        $db = getDatabase();

        $previous_submission = getSubmissionFile($db, $_SESSION["user"], $assignment_id);

        $storage = new \Upload\Storage\FileSystem($_SERVER['DOCUMENT_ROOT'] . '/assets/submissions/');
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

    /*
     * Questionnaires
     *      Provides access to questionnaires
     */
    $router->get("/questionnaire/", function (){
        $bp = getBootstrap();
        session_start();

        // Get data
        $data = getQuestionnaires(getDatabase(), $_SESSION['school']);
        $columns = [["Titel", "name"]];    // TODO: rename name column to title

        // Generate menu
        $menu = generateMenu($bp, ["active" => "Vragenlijsten", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["name"] => "#", "Vragenlijsten" => "#"]);
        $link = '<a href="%s/">%s</a>';
        $options = [
            ["<a class='' href='%s/'><i class='glyphicon glyphicon-pencil'></i> Invullen</a>"],
        ];


        // Generate page
        echo getTemplates()->render("questionnaires::index", [
            "title" => "Tekstmijn | Vragenlijsten",
            "page_title" => "Vragenlijsten",
            "table" => generateTable($bp, $columns, $data, $options, $link),
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
        ]);
    });

    $router->get("/questionnaire/(\d+)/", function ($questionnaire_id){
        $bp = getBootstrap();
        $db = getDatabase();
        session_start();

        $q_name = getQuestionnaireName($db, $questionnaire_id);

        // Generate menu
        $menu = generateMenu($bp, ["active" => "Vragenlijsten", "align" => "stacked"]);
        $breadcrumbs = generateBreadcrumbs($bp, [$_SESSION["name"] => "#",
            "Vragenlijsten" => "../../questionnaire/",
            sprintf("%s", $q_name)=> "#"]);

        // Generate page
        echo getTemplates()->render("questionnaires::questionnaire", [
        "title" => "Hofstad | Vragenlijst",
            "menu" => $menu,
            "breadcrumbs" => $breadcrumbs,
            "db" => $db,
            "school" => $_SESSION['school'],
            "student" => $_SESSION['id']
        ]);

    });

    $router->post("/questionnaire/(\d+)/saveques", function () {
        $db = getDatabase();

        session_start();
        $student_id = $_POST['student_id'];
        $questionnaire_id = $_POST['questionnaire_id'];
        $saved_data = $_POST;
        unset($saved_data['student_id']);
        unset($saved_data['questionnaire_id']);

        $result = save_questionnaire($db, $saved_data, $student_id, $questionnaire_id);
            if ($result){
                getRedirect("../?success=true");
            } else {
                getRedirect("../?success=false");
            }
    });

    $router->get('/megaupload', function (){
       echo getTemplates()->render("megaupload");
    });

    $router->post('/megaupload/', function (){
        $storage = new \Upload\Storage\FileSystem($_SERVER['DOCUMENT_ROOT'] . '/assets/submissions/');
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
            $rows_affected = setSubmission(getDatabase(), $_POST["student"], $_POST["assignment"], $originalfilename, $db_filename);
            echo "Done!";
        } catch (\Exception $e) {
            print_r($e);
        }
    });

    $router->run();