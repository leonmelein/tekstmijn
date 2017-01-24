<?php

function getQuestionnaires($database, $classid){
    $quoted_id = $database->quote($classid);
    $querystring = "SELECT *
                    FROM questionnaires
                    WHERE id in (
                      SELECT questionnaire_id
                      FROM questionnaires_classes
                      WHERE class_id = $quoted_id
                    )";

    return $database->query($querystring)->fetchAll();
}

function getAllQuestions($database, $questionnaireid){
    $quoted_id = $database->quote($questionnaireid);
    $querystring = "SELECT id, question
    FROM questions, questionnaire_question
    WHERE questionnaire_question.question_id = questions.id
    AND questionnaire_id = $quoted_id";

    return $database->query($querystring)->fetchAll();
}

