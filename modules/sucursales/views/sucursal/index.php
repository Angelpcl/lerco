<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Sucursales';
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
  <?= $can['create']?
            Html::a('Nueva sucursal', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li>
            <a class="nav-link active" data-toggle="tab" href="#tab-index">Sucursales MX</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-sucursal-usa">Sucursales USA</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="tab-index" role="tabpanel" class="tab-pane active">
            <?= $this->render('_index_mex',[
                "can"   => $can
                ]) ?>
        </div>
        <div role="tabpanel" id="tab-sucursal-usa" class="tab-pane">
                <?= $this->render('_index_usa',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
