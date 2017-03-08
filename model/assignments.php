<?php

    function getAssignments($database, $studentid){
        $quoted_id = $database->quote($studentid);
        $querystring = "SELECT id, title, status, DATE_FORMAT(end_date, '%d %M %Y %H:%i') AS end_date
                        FROM (
                            SELECT assignments.id AS id, assignments.title AS title,
                              IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
                                'Open', 'Gesloten') AS status,
                                 assignments_class.end_date AS end_date
                            FROM assignments, assignments_class, students
                            WHERE students.id = $quoted_id
                            AND students.class_id = assignments_class.class_id
                            AND assignments_class.assignment_id = assignments.id
                            AND assignments_class.start_date <= NOW()
                        ) AS classwork";

        return $database->query($querystring)->fetchAll();
    }

    function getAssignment($database, $assignment_id, $class_id){
        $quoted_id = $database->quote($assignment_id);
        $quoted_class = $database->quote($class_id);

        $querystring = "SELECT title, IF(NOW() BETWEEN assignments_class.start_date AND assignments_class.end_date,
            'Open', 'Gesloten') AS status, DATE_FORMAT(end_date, '%d %M %Y %H:%i') as deadline
                        FROM assignments, assignments_class
                        WHERE assignments.id = assignments_class.assignment_id
                        AND class_id = $quoted_class
                        AND assignments.id = $quoted_id";

        return $database->query($querystring)->fetchAll()[0];
    }

    function generateTabs($bp, $active = 'Info'){
        return $bp->tabs(array(
            'Info' => '#info',
            'Inzending' => '#submission',
        ), array(
            'active' => $active,
            'toggle' => "tab",
        ));
    }

