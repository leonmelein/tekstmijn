<?php

function getStudents($database){
    return $database->select("students", ["id", "firstname", "lastname"]);
}

function generateTable($bp, $columns, $data, $classes = "class=responsive hover"){
    $table = $bp->table->open($classes);
    $table .= $bp->table->head();
    foreach ($columns as $column){
        $table .= $bp->table->cell('', $column[0]);
    }

    foreach ($data as $item) {
        $table .= $bp->table->row();
        foreach ($columns as $column) {
            $table .= $bp->table->cell('', $item[$column[1]]);
        }
    }

    $table .= $bp->table->close();
    return $table;
}