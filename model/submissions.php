<?php
use Carrooi\DocxExtractor\DocxExtractor;


function setSubmission($database, $student_id, $assignment_id, $originalfilename, $file){
    $extractor = new DocxExtractor;
    $text = $extractor->extractText('/volume1/hofstad/assets/submissions/' . $file);

    $rows_affected = $database->insert("submissions",
                                        ["student_id" => $student_id,
                                        "assignment_id" => $assignment_id,
                                        "file" => $file,
                                        "original_file" => $originalfilename,
                                        "text" => $text
                                        ]
    );
    return $rows_affected;
}

function updateSubmission($database, $student_id, $assignment_id, $originalfilename, $file, $previous){
    $is_deleted = unlink("/volume1/hofstad/assets/submissions/" . $previous);

    $extractor = new DocxExtractor;
    $text = $extractor->extractText('/volume1/hofstad/assets/submissions/' . $file);

    $rows_affected = $database->update("submissions",
        ["file" => $file,
            "original_file" => $originalfilename,
            "text" => $text,
            "submission_count[+]" => 1
        ], ["AND" => [
            "student_id" => $student_id,
            "assignment_id" => $assignment_id
        ]]
    );

    return $rows_affected;
}

function getSubmission($database, $student_id, $assignment_id){
    $quoted_student = $database->quote($student_id);
    $quoted_assignment = $database->quote($assignment_id);
    $query = "SELECT DATE_FORMAT(time, '%d %M %Y, %H:%i') AS time, file, original_file
              FROM submissions
              WHERE student_id = $quoted_student
              AND assignment_id = $quoted_assignment";

    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];
}

function getSubmissionFile($database, $student_id, $assignment_id){
    $quoted_student = $database->quote($student_id);
    $quoted_assignment = $database->quote($assignment_id);
    $query = "SELECT file
              FROM submissions
              WHERE student_id = $quoted_student
              AND assignment_id = $quoted_assignment";
    return $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['file'];
}