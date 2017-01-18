<?php

function getAssignments($database, $studentid){

    $quoted_id = $database->quote($studentid);
    $querystring = "SELECT id, title, status, DATE_FORMAT(end_date, '%d %M %Y %H:%i') AS end_date
                    FROM (
                        SELECT assignments.id AS id, assignments.title AS title,
                          IF(assignments_class.start_date < curdate() < assignments_class.end_date,
                             'Open', 'Gesloten') AS status,
                             assignments_class.end_date AS end_date
                        FROM assignments, assignments_class, students
                        WHERE students.id = $studentid
                        AND students.class_id = assignments_class.class_id
                        AND assignments_class.assignment_id = assignments.id
                    ) AS classwork
                    WHERE id NOT IN (
                      SELECT assignment_id
                      FROM submissions
                      WHERE student_id = $studentid
                    )";

    return $database->query($querystring)->fetchAll();
}