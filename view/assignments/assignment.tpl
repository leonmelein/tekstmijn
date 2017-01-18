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
                <br/>
                <h4>Status</h4>
                <p>Dingen</p>
                <h4>Uiterste inleverdatum</h4>
                <p>voor de zomervakantie ergens is wel prima, gr. george.</p>
            </div>
            <div id="submission" class="tab-pane">
                <br/>
                <h4>Inzendingen</h4>
            </div>
        </div>
    </div>
</div>
