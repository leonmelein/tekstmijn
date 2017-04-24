<?php $this->layout('main_layout', ['title' => $title]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php
            if($_GET["success"] == "true") {
                echo '<div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
        <strong>Gelukt.</strong> Je antwoorden zijn opgeslagen.
    </div>';
    } else if($_GET["success"] == "true") {
    echo '<div class="alert alert-success alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Sluiten"><span aria-hidden="true">&times;</span></button>
        <strong>Oeps.</strong> Je antwoorden konden niet worden verwerkt. Probeer het nogmaals of vraag je docent.
    </div>';
    }
    ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <?php generateQuestionnaire($db, $school, $student); ?>
    </div>
</div>
