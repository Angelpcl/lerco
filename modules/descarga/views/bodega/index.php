<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Descarga de trailer [DISTRIBUIDORAS]';
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
  <?= Html::a('Nueva configuración', ['create'], ['class' => 'btn btn-success add'])  ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-index">BODEGA CENTRAL</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-bodega-puebla">BODEGA PUEBLA</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-bodega-oaxaca">BODEGA OAXACA</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab-index" role="tabpanel" class="tab-pane active">
            <?= $this->render('_bodega_juan') ?>
        </div>
        <div role="tabpanel" id="tab-bodega-puebla" class="tab-pane">
                <?= $this->render('_bodega_puebla') ?>
        </div>
        <div role="tabpanel" id="tab-bodega-oaxaca" class="tab-pane">
                <?= $this->render('_bodega_oaxaca') ?>
        </div>
    </div>
</div>
