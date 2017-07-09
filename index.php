<?php $debug = false;
    /**
     * CONTROLLER
     */

    // Third party libraries
    require("vendor/autoload.php");

    // Debugging functionality
    if ($debug) {
        error_reporting(E_ALL);
    }

    // Main model
    require("model/model.php");

    // Page models
    require("model/auth.php");
    require("model/assignment.php");
    require("model/questionnaire.php");
    require("model/megaupload.php");

    // Reroute HTTP traffic to HTTPS
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

    // Initiate router
    $router = new \Bramus\Router\Router();

    /*
     * Page routers
     *
     * Provides routes to the individual parts of the system
     */

    /**
     * Authentication
     *
     * Provides routes for logging on and off, as well as registering a new account.
     */
    //  Authentication check: check if each request has a user ID set in session.
    $router->before('GET|POST', '/assignment/.*', "auth@auth_check");
    $router->before('GET|POST', '/assignment/', "auth@auth_check");
    $router->before('GET|POST', '/questionnaire/.*', "auth@auth_check");
    $router->before('GET|POST', '/questionnaire/', "auth@auth_check");

    /**
     * Auth
     *
     * Handles authentication and registration of students.
     */
    $router->get("/", "auth@login");
    $router->mount("/login", function() use ($router){
        $router->get("/", "auth@loginpage");
        $router->post("/", "auth@performLogin");
    });
    $router->get('/logout/', "auth@performLogout");
    $router->get("/register/", "auth@startRegistration");
    $router->post("/register/", "auth@finishRegistration");

    /**
     * Assignments
     *
     * Provides access to assignments and enables submission.
     */
    $router->mount('/assignment', function() use ($router){
        $router->get("/", "assignment@overview");
        $router->get("/(.*)/", "assignment@individualAssignment");
        $router->post("/(.*)/submit", "assignment@submitAssignment");
    });

    /**
     * Questionnaires
     *
     * Provides access to questionnaires.
     */
    $router->get("/questionnaire/", "questionnaire@overview");

    /**
     * Megaupload
     *
     * Provides a POST interface for batch uploading of missing assignment.
     * For developer use only.
     */
    $router->post('/megaupload/', "megaupload@upload");

    $router->run();