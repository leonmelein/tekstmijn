<?php

function getStudents($database){
    return $database->select("students", ["id", "firstname", "lastname"]);
}

function generateTable($bp, $columns, $data, $options = null, $format = "", $classes = "class=responsive hover", $external = false){
    if ($classes === null) {
        $classes = "class=responsive hover";
    }

    $table = $bp->table->open($classes);
    $table .= $bp->table->head();
    foreach ($columns as $column){
        $table .= $bp->table->cell('', $column[0]);
    }

    if (isset($options)){
        foreach ($options as $option){
            $table .= $bp->table->cell('', $option[2]);
        }
    }

    foreach ($data as $item) {
        $table .= $bp->table->row();

        foreach ($columns as $column) {
            if(empty($format)){
                $table .= $bp->table->cell('', $item[$column[1]]);
            } else {
                if (!$external){
                    $table .= $bp->table->cell('', sprintf($format,
                        $item['id'],
                        $item[$column[1]]
                    ));
                } else {
                    $table .= $bp->table->cell('', sprintf($format,
                        $item['qualtrics_url'],
                        $item[$column[1]]
                    ));
                }
            }

        }

        if (isset($options)) {
            foreach ($options as $option) {
                if ($external) {
                    $table .= $bp->table->cell('', sprintf($option[0], $item["qualtrics_url"], $item["qualtrics_url"]
                    ));
                } else {
                    $table .= $bp->table->cell('', sprintf($option[0], $item["id"], $item["id"]
                    ));
                }
            }
        }
    }

    $table .= $bp->table->close();
    return $table;
}

function generateMenu($bp, $active){
    $menu_panel = '<div class="panel panel-default">
                       <div class="panel-heading">Menu</div>
                       <div class="panel-body">%s</div>
                    </div>';

    $menu_options = ["Opdrachten" => "/assignment/", "Vragenlijsten" => "/questionnaire/"];
//    $menu_options = ["Vragenlijsten" => "/questionnaire/"];
    return sprintf($menu_panel, $bp->pills($menu_options, $active));
}

function generateBreadcrumbs($bp, $path){
    return $bp->breadcrumbs($path);
}