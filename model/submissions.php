<?php
use Carrooi\DocxExtractor\DocxExtractor;

function setSubmission($database, $student_id, $assignment_id, $file){
    $extractor = new DocxExtractor;
    $text = $extractor->extractText('/volume1/hofstad/assets/submissions/' . $file);

    $rows_affected = $database->insert("submissions",
                                        ["student_id" => $student_id,
                                        "assignment_id" => $assignment_id,
                                        "file" => $file,
                                        "text" => $text
                                        ]
    );
    return $rows_affected;
}

function getSubmission($database, $student_id, $assignment_id){
    $quoted_student = $database->quote($student_id);
    $quoted_assignment = $database->quote($assignment_id);
    $query = "SELECT DATE_FORMAT(time, '%d %M %Y, %H:%i') AS time, file
              FROM submissions
              WHERE student_id = $quoted_student
              AND assignment_id = $quoted_assignment";

    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}