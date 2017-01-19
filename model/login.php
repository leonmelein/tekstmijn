<?php

function check_login($database, $username, $password){
    $quoted_username = $database->quote($username);
    $query = "SELECT password, class_id FROM students WHERE id = $quoted_username";
    $user = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];

    return hash_equals($user['password'], crypt($password, $user['password']));
}

function getUserInfo($database, $username){
    $quoted_username = $database->quote($username);
    $query = "SELECT CONCAT_WS(' ', firstname, prefix, lastname) as name, class_id as class FROM students WHERE id = $quoted_username";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function hash_password($password){
    $cost = 10;
    $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), "+", ".");
    $salt = sprintf("$2a$%02d$", $cost) . $salt;

    $hash = crypt($password, $salt);
    return $hash;
}

function set_initial_password($database, $username, $hash){
    $rows_affected = $database->update("students",
        ["password" => hash_password($hash)],
        ["AND" =>
            [
                "id" => $username,
                "password" => null
            ]
        ]
    );

    return $rows_affected;
}