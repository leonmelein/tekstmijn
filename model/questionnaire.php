<?php

/**
 * Shows open questionnaires to students.
 */
class questionnaire extends model {

    /**
     * Renders the questionnaire overview
     */
    function overview(){
        $this->get_session();
        $studentid = $_SESSION['id'];

        // Get available questionnaires
        $data = $this->getQuestionnaires($_SESSION['school']);
        $columns = [["Titel", "title"]];

        // Generate navigation items
        $menu = $this->menu($this->bootstrap, ["active" => "Vragenlijsten", "align" => "stacked"]);
        $breadcrumbs = $this->breadcrumbs($this->bootstrap, [$_SESSION["name"] => "#", "Vragenlijsten" => "#"]);
        $link = '<a href="%s?student_id=' .$studentid. '" target="_blank">%s</a>';
        $options = [
            ["<a class='' href='%s' target='_blank'><i class='glyphicon glyphicon-pencil'></i> Invullen</a>"],
        ];

        // Generate page
        echo $this->templates->render("questionnaires::index",
            [
                "title" => "Tekstmijn | Vragenlijsten",
                "page_title" => "Vragenlijsten",
                "table" => $this->table($this->bootstrap, $columns, $data, $options, $link, null, true),
                "menu" => $menu,
                "breadcrumbs" => $breadcrumbs,
            ]
        );
    }

    /*
     * Supporting functions
     */

    /**
     * Retrieves available questionnaires from the database.
     *
     * @param $schoolid Int containing the institution's ID.
     * @return mixed False if there are no questionnaires available or an array containing all title and url attributes.
     */
    function getQuestionnaires($schoolid){
        return $this->database->select("questionnaire", "*", ["school_id" => $schoolid]);
    }

}