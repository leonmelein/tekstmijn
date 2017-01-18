<?php

function getStudents($database){
    return $database->select("students", ["id", "firstname", "lastname"]);
}

function generateTable($bp, $columns, $data, $format = "", $classes = "class=responsive hover"){
    $table = $bp->table->open($classes);
    $table .= $bp->table->head();
    foreach ($columns as $column){
        $table .= $bp->table->cell('', $column[0]);
    }

    foreach ($data as $item) {
        $table .= $bp->table->row();
        foreach ($columns as $column) {
            $table .= $bp->table->cell('', sprintf($format,
                                                    $item['id'],
                                                    $item[$column[1]]
            ));
//            if (strcmp($format, "")){
//                $table .= $bp->table->cell('', $item[$column[1]]);
//            } else {
//                $table .= sprintf(
//                    $format,
//                    $item['id'],
//                    $bp->table->cell('', $item[$column[1]])
//                );
//            }
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

    $menu_options = ["Opdrachten" => "/assignments/", "Vragenlijsten" => "/questionnaires/"];
    return sprintf($menu_panel, $bp->pills($menu_options, $active));
}

function generateBreadcrumbs($bp, $path){
    return $bp->breadcrumbs($path);
}