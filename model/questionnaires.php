<?php

function getQuestionnaires($database, $schoolid){
    $quoted_id = $database->quote($schoolid);
    $querystring = "SELECT *
                    FROM questionnaires
                    WHERE school_id = $quoted_id";

    return $database->query($querystring)->fetchAll();
}

function getQuestionnaireName($database, $questionnaire_id){
    return $database->get("questionnaires", "name", ["id" => $questionnaire_id]);
}

function generateQuestionnaire($database, $school_id, $student_id) {
    $student_id_quoted = $database->quote($student_id);
    $school_id_quoted = $database->quote($school_id);

    // Get questionnaire
    $query = "SELECT id, name, action, method
                FROM questionnaires
                WHERE questionnaires.school_id = $school_id_quoted";
    $questionnaire = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0];

    // Get questions for questionnaire
    $questionnaire_id_quoted = $database->quote($questionnaire['id']);
    $query = "SELECT id, elementtype, label
                FROM questionnaires_questions
                WHERE questionnaires_questions.questionnaire_id = $questionnaire_id_quoted";
    $questions = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Generate form
    echo "<h1>".$questionnaire['name']."</h1>";
    Form::open ($questionnaire['id'], $values = NULL, $attributes = Array("method" => $questionnaire['method'], "action" => $questionnaire['action']));
    Form::Hidden ("student_id", $values = $student_id, $attributes = NULL);
    Form::Hidden ("questionnaire_id", $values = $questionnaire['id'], $attributes = NULL);
    foreach ($questions as $id => $value) {
        $elementtype = $value['elementtype'];
        $id = $value['id'];
        $question_id_quoted = $database->quote($id);
        $label = $value['label'];

        // Get attributes for questions
        $query = "SELECT attribute_key, attribute_value
                FROM questionnaire_question_attributes
                WHERE questionnaire_question_attributes.questionnairesquestions_id = $question_id_quoted";
        $attributes_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $attributes_local = Array();
        if (!empty($attributes_db)){
            foreach ($attributes_db as $key => $value){
                $attributes_local[$value['attribute_key']] = $value['attribute_value'];
            }
        }

        // Get options for questions
        $query = "SELECT option_key, option_value
                FROM questionnaire_question_options
                WHERE questionnaire_question_options.questionnairesquestions_id = $question_id_quoted";
        $options_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC);
        $options_local = Array();
        if (!empty($options_db)){
            foreach ($options_db as $key => $value){
                $options_local[$value['option_value']] = $value['option_key'];
            }
        }

        // Get saved values for questions
        $query = "SELECT value
                FROM questioning
                WHERE questioning.student_id = $student_id_quoted
                AND questioning.questionnaire_id = $questionnaire_id_quoted
                AND questioning.question_id = $question_id_quoted";

        $values_db = $database->query($query)->fetchAll(PDO::FETCH_ASSOC)[0]['value'];

        if ($values_db != ""){
            $attributes_local['value'] = $values_db;
        }

        if ($elementtype == 'title'){
            echo "</br>";
            echo "<h5>".$label."</h5>";
        }
        elseif ( in_array($elementtype, Array('YesNo', 'Number')) ){
            Form::$elementtype ($label, $id, $attributes_local);
        }
        else{
            Form::$elementtype ($label, $id, $options_local, $attributes_local);
        }
    }
    Form::Button ("Opslaan");
    Form::close (false);

}

function save_questionnaire($database, $values, $student_id, $questionnaire_id) {
    $database->delete("questioning", [
        "AND" => [
            "student_id" => $student_id,
            "questionnaire_id" => $questionnaire_id
        ]
    ]);
    foreach( $values as $key => $value){

        $result = $database->insert("questioning", [
            "student_id" => $student_id,
            "questionnaire_id" => $questionnaire_id,
            "question_id" => $key,
            "value" => $value
        ]);
    }

    return $result;
}

