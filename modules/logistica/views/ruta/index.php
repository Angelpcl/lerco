<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Rutas';
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= $can['create']?
            Html::a('Nueva ruta', ['create'], ['class' => 'btn btn-success add']): '' ?>
</p>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist">
        <li class="active">
            <a class="nav-link active" data-toggle="tab" href="#tab-index">Rutas Base</a>
        </li>
        <li>
            <a class="nav-link" data-toggle="tab" href="#tab-ruta-foranea">Rutas Foraneas</a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" id="tab-index" class="tab-pane active">
            <?= $this->render('_index_base',[
                "can"   => $can
                ]) ?>
        </div>
        <div role="tabpanel" id="tab-ruta-foranea" class="tab-pane">
                <?= $this->render('_index_foranea',[
                "can"   => $can
                ]) ?>
        </div>
    </div>
</div>
