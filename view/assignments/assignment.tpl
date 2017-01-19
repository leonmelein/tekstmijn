<?php $this->layout('main_layout', ['title' => $title, 'pageJS' => $page_js]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
        <?php
            if($_GET["upload"] == "success") {
                echo '<div class="alert alert-success alert-dismissable" role="alert">
                           <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                           <strong>Gelukt.</strong> Je inzending is ontvangen.
                      </div>';
            } else if ($_GET["upload"] == "failed") {
                echo '<div class="alert alert-danger alert-dismissable" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <b>Ai.</b> Je inzending kon niet worden opgeslagen. Probeer het nog eens of vraag je docent.
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
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <?php echo $tabs; ?>
        <!-- <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#info">Info</a></li>
            <li><a data-toggle="tab" href="#submission">Inzending</a></li>
        </ul> -->

        <div class="tab-content">
            <div id="info" class="tab-pane active">
                <h4 class="assignment_info">Status</h4>
                <p><?=$this->e($status)?></p>
                <h4 class="assignment_info">Uiterste inleverdatum</h4>
                <p><?=$this->e($deadline)?></p>
            </div>
            <div id="submission" class="tab-pane">
                <?php
                if(isset($submission)) {
                    $submission_format = '<h4 class="assignment_info">Je inzending</h4>
                         <div class="row">
                            <div class="col-md-2"><strong>Datum</strong></div>
                            <div class="col-md-4">%s</div>
                         </div>
                         <div class="row">
                            <div class="col-md-2"><strong>Bestand</strong></div>
                            <div class="col-md-4"><a href="/assets/submissions/%s" target="_blank">%s</a></div>
                         </div>';

                    echo sprintf($submission_format,
                        $submission['time'],
                        $submission['file'],
                        $submission['original_file']);
                }
                ?>

                <?php if ($status == "Open"){ ?>
                   <h4 class="assignment_info">Nieuwe inzending</h4>
                    <form class="form-horizontal" action="submit/" method="post" enctype="multipart/form-data">
                        <fieldset>
                            <div class="form-group" style="display: <?php echo ($overwrite == 1 ? 'inherit;' : 'none;'); ?>">
                                <div class="col-md-6">
                                    <p class="form-control-static overwrite-warning"><span class="glyphicon glyphicon-info-sign"></span> Je eerdere inzending wordt overschreven.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-2 control-label" for="file">Bestand</label>
                                <div class="col-md-4">
                                    <input id="file" name="file" class="input-file" type="file" required>
                                    <span class="help-block">Selecteer een .docx-document van maximaal 5 MB</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label" for="submit"></label>
                                <div class="col-md-4">
                                    <button id="submit" name="submit" type="submit" class="btn btn-primary">Uploaden</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                <?php } ?>

            </div>
        </div>
    </div>
</div>