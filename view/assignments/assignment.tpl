<?php $this->layout('main_layout', ['title' => $title]); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $breadcrumbs; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <?php echo $menu; ?>
    </div>
    <div class="col-md-9">
        <h1 class="page_title"><?php echo $page_title; ?></h1>
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#info">Info</a></li>
            <li><a data-toggle="tab" href="#submission">Inzending</a></li>
        </ul>

        <div class="tab-content">
            <div id="info" class="tab-pane active">
                <h4 class="assignment_info">Status</h4>
                <p><?=$this->e($status)?></p>
                <h4 class="assignment_info">Uiterste inleverdatum</h4>
                <p><?=$this->e($deadline)?></p>
            </div>
            <div id="submission" class="tab-pane">
                <h4 class="assignment_info">Inzendingen</h4>
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="file">Bestand</label>
                            <div class="col-md-4">
                                <input id="file" name="file" class="input-file" type="file">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label" for="submit"></label>
                            <div class="col-md-4">
                                <button id="submit" name="submit" class="btn btn-primary">Uploaden</button>
                            </div>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>
    </div>
</div>
