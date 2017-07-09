<?php

/**
 * Handles authentication and registration of students.
 */
class auth extends model {

    /**
     * Redirects root to login page.
     */
    function login(){
        $this->redirect("/login");
    }

    /**
     * Renders login page.
     */
    function loginpage(){
        echo $this->templates->render("login::login", ["title" => "Tekstmijn | Inloggen"]);
    }

    /**
     * Handles user login.
     */
    function performLogin(){
        if($this->check_login($_POST['username'], $_POST['password'])){
            $this->get_session();
            $userinfo = $this->getUserInfo($_POST['username']);
            $_SESSION['user'] = $_POST['username'];
            $_SESSION['class'] = $userinfo["class"];
            $_SESSION['school'] = $userinfo["school"];
            $_SESSION['name'] = $userinfo["name"];
            $_SESSION['id'] = $userinfo["id"];
            $this->redirect("/questionnaire/");
        } else {
            $this->redirect("/login/?failed=true");
        }
    }

    /**
     * Handles user logout.
     */
    function performLogout(){
        session_start();
        session_destroy();
        $this->redirect("/login/?logged_out=true");
    }

    /**
     * Starts registration process and renders registration form.
     */
    function startRegistration(){
        echo $this->templates->render("login::register", ["title" => "Tekstmijn | Registreren"]);
    }

    /**
     * Finishes registration process by setting the user's initial password.
     */
    function finishRegistration(){
        if($this->set_initial_password($_POST["username"], $_POST["password"])){
            $this->redirect("/login/?registration=true");
        } else {
            $this->redirect("/register/?failed=true");
        }
    }

    /*
     * Supporting functions
     */

    /**
     * Checks if the given credentials match a known user in the database.
     *
     * @param $username String containing the username
     * @param $password String containing the password
     * @return bool Boolean indicating if the credentials are valid
     */
    function check_login($username, $password){
        $quoted_username = $this->database->quote($username);
        $query = "SELECT password, class_id FROM students WHERE id = $quoted_username";
        $user = $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
        return hash_equals($user['password'], crypt($password, $user['password']));
    }

    /**
     * Retrieves user info for a given user.
     *
     * @param $username String containing the username
     * @return mixed array containing the name, school and class of the student in question or False in case there is
     * no matching user to be found
     */
    function getUserInfo($username){
        $quoted_username = $this->database->quote($username);
        $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, school_id as school, id, class_id as class FROM students WHERE id = $quoted_username";
        return $this->database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
    }

    /**
     * Hashes the password with salt on first registration to enable safe storage and login.
     *
     * @param $password String containing the chosen password
     * @return string containing the hashed, salted password for storage in the user database
     */
    function hash_password($password){
        $cost = 10;
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), "+", ".");
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        $hash = crypt($password, $salt);
        return $hash;
    }

    /**
     * Sets the password for a user upon first registration.
     *
     * @param $username String containing the username
     * @param $password String containing the password
     * @return bool|int 1 if the password was succesfully set or False if it failed
     */
    function set_initial_password($username, $password){
        $rows_affected = 0;

        if (strlen($password) > 0){
            $rows_affected = $this->database->update("students",
                ["password" => $this->hash_password($password)],
                ["AND" =>
                    [
                        "id" => $username,
                        "password" => null
                    ]
                ]
            );
        }


        return $rows_affected;
    }

    /**
     * Checks if a user is (still) logged in. If not, the user is redirected to the login page.
     */
    function auth_check(){
        session_start();
        if (!isset($_SESSION['user'])) {
            $this->redirect("/login");
            exit();
        }
    }

}